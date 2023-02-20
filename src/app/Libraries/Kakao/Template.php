<?php

namespace App\Libraries\Kakao;

use App\Enums\UserEnum;
use App\Libraries\Helper;
use App\Models\Campaign;
use App\Models\User;
use App\Repositories\Api\CampaignWorkflowRepository;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class Template
{
    public static function prepareAlimData($trigger, $userId, $campaign, $metaData = [])
    {
        $userInfo = User::find($userId);
        if (!$userInfo || !$userInfo->contact) {
            // Log::addError('[kakaoTemplate] no contact.', ['user' => $userInfo]);
            return null;
        }
        if ($userInfo->state == UserEnum::STATE_DELETED_TEMPORARILY) {
            // Log::addError('[kakaoTemplate] user withdraw.', ['user' => $userInfo]);
            return null;
        }
        if (!Helper::isMarketSelect($userInfo)) {
            // Log::addError('[kakaoTemplate] only support for KR.');
            return null;
        }
        if (!Utils::checkUserAvailable($userId, $trigger)) {
            // RSA-516
            // Log::addInfo("[kakaoTemplate] SKIPPED: user[{$userId}] is using App push notification instead.");
        }

        // Log::addInfo('[kakaoTemplate][trigger]'.$trigger);

        $alimConfig = self::getAtkConfigureFromTrigger($trigger);
        if (empty($alimConfig)) {
            // Log::addWarning('[kakaoTemplate] empty $alimConfig.');
            return null;
        }

        app()->configure('services');
        $config = config('services.kakao');

        $rawMsg = Arr::get($alimConfig, 'message');

        if ($campaign && $campaign instanceof Campaign) {
            CampaignWorkflowRepository::applyExtendDeadline($campaign, $userInfo);
        }

        $alimConfig['message'] = self::renderMessage($rawMsg, $userInfo, $campaign, $metaData);

        $buttons = Arr::get($alimConfig, 'buttons', []);
        unset($alimConfig['buttons']);
        $buttons = self::processAttachmentButtons($buttons, [
            'inquiryHash' => Arr::get($metaData, 'inquiryHash'),
            'campaignHash' => $campaign ? $campaign->hash : null,
        ]);
        if (!empty($buttons)) {
            $alimConfig['attachment'] = (object)['button' => $buttons];
        }

        return array_merge($alimConfig, [
            'sender_key' => $config['sender_key'],
            'callback_number' => $config['callback_number'],
            'phone_number' => $userInfo->contact,
            'calling_code' => Helper::removeNonNumeric($userInfo->phone_code),
            'campaign_id' => $campaign ? $campaign->id : null,
            'trigger' => $trigger,
        ]);
    }

    /**
     * @param string $trigger REVU trigger
     * @return array|null
     */
    protected static function getAtkConfigureFromTrigger($trigger)
    {
        // REVU trigger => kakao_alim key
        $tempateMap = [
            'inquiry-answered-new' => 'user-inquiry',
            'campaign-offered-new' => 'user-Offer-campaign',
            'campaign-selected-new' => 'campaign-selected-basics',
            'campaign-draft-selected' => 'campaign-selected-draft',
            'campaign-shipping-apply' => 'user-campaign-invoice',
            'campaign-shipping-apply-new' => 'user-campaign-invoice',
            'workflow-draft-hurry-up' => 'user-campaign-draft-dday',
            'workflow-draft-revised-hurry-up' => 'user-campaign-draft-edit',
            // 'request-revised-influencer-operator-draft-new' => 'user-campaign-draft-edit',
            'campaign-content-hurry-up' => 'user-campaign-content-dday',
        ];

        $atkKey = Arr::get($tempateMap, $trigger);
        if (!$atkKey) {
            // Log::addInfo("[KakaoTemplate] SKIPPED: trigger[$trigger] not supported with kakaoAtk yet.");
            return null;
        }

        app()->configure('kakao_alim');
        $atkConfigs = config('kakao_alim');
        $setting = Arr::get($atkConfigs, $atkKey);
        if (!$setting) {
            Log::error("[KakaoTemplate] Setting for key[$atkKey] is not found.");
            return null;
        }

        return $setting;
    }

    protected static function renderMessage($rawMsg, $userInfo, $campaign, $metaData)
    {
        $campaign = $campaign instanceof Campaign ? $campaign : new Campaign(); // fix access prop on null

        if ($campaign->id) {
            $postStartDate = Carbon::createFromFormat('Y-m-d', $campaign->selected_date)->addDays(1);
            $contentStartDate = $campaign->selected_date;
            if ($campaign->workflow) {
                $contentStartDate = $campaign->workflow_video_date ?: $campaign->workflow_draft_date;
            }

            $contentStartDate = Carbon::createFromFormat('Y-m-d', $contentStartDate);
        } else {
            $postStartDate = $contentStartDate = Carbon::now(); // fix InvalidArgumentException
        }

        $draftSubmitTo = $campaign->workflow_draft_submit_date;
        $draftReviewTo = $campaign->workflow_draft_date;

        // $videoSubmitTo = $campaign->workflow_video_submit_date;
        // $videoReviewTo = $campaign->workflow_video_date;

        $points = self::displayPoints($campaign->points, Arr::get($metaData, 'additionalPoint'));

        $messageDict = [
            '#{user_nickname}' => $userInfo->nickname,
            '#{campaign_name}' => $campaign->title,
            '#{product_name}' => Arr::get($metaData, 'productName'),
            '#{application_deadline}' => $campaign->requested_date,
            '#{points}' => $points,
            '#{offer_message}' => Arr::get($metaData, 'offerMessage'),
            '#{post_startdate}' => $postStartDate->format('Y-m-d'),
            '#{post_deadline}' => $campaign->posted_date,
            '#{courier_service}' => Arr::get($metaData, 'courier_service'),
            '#{invoice_number}' => Arr::get($metaData, 'invoice_number'),
            '#{content_startdate}' => $contentStartDate->addDays(1)->format('Y-m-d'),
            '#{content_deadline}' => $campaign->posted_date,
            '#{draft_submission_deadline}' => $draftSubmitTo,
            '#{draft_review_deadline}' => $draftReviewTo,
        ];

        return str_replace(array_keys($messageDict), array_values($messageDict), $rawMsg);
    }

    protected static function displayPoints($basicPoint, $additionalPoint = null)
    {
        $points = [];
        $basicPoint = $basicPoint ? number_format((float)$basicPoint) . 'P' : null; // 1,000P | null
        $basicPoint && array_push($points, $basicPoint);

        $additionalPoint = $additionalPoint ? number_format((float)$additionalPoint) . 'P' : null; // 1,000P | null
        $additionalPoint && array_push($points, $additionalPoint);
        return count($points) > 1 ? implode(' + ', $points) : implode('', $points);
    }

    protected static function processAttachmentButtons($buttons, $hashes)
    {
        foreach ($buttons as &$button) {
            if (!empty($button['url_mobile'])) {
                $button['url_mobile'] = self::getShortLink($button['url_mobile'], $hashes);
            }
            $button = (object)$button;
        }

        return $buttons;
    }

    protected static function getShortLink($templateLink, $hashes = [])
    {
        if (strpos($templateLink, '/inquiry') !== false) {
            return Helper::getShortLinkFirebase(Arr::get($hashes, 'inquiryHash'), [
                'domainUriPrefix' => 'https://links.select.revu.net/inquiry',
                'urlPath' => 'question-answer',
            ]);
        }

        if (strpos($templateLink, '/campaign/draft') !== false) {
            return Helper::getShortLinkFirebase(Arr::get($hashes, 'campaignHash'), [
                'domainUriPrefix' => 'https://links.select.revu.net/campaign/draft',
                'urlSuffix' => 'draft',
            ]);
        }

        if (strpos($templateLink, '/campaign/post') !== false) {
            return Helper::getShortLinkFirebase(Arr::get($hashes, 'campaignHash'), [
                'domainUriPrefix' => 'https://links.select.revu.net/campaign/post',
                'urlSuffix' => 'posting',
            ]);
        }

        if (strpos($templateLink, '/campaign') !== false) {
            return Helper::getShortLinkFirebase(Arr::get($hashes, 'campaignHash'), [
                'domainUriPrefix' => 'https://links.select.revu.net/campaign',
            ]);
        }

        return null;
    }
}
