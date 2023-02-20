<?php

namespace App\Libraries;

use App\Enums\CampaignEnum;
use App\Models\Campaign;
use App\Models\InstagramUser;
use App\Models\User;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * Class ChatBuzz
 */
class ChatBuzz
{
    public const TYPE_SYSTEM = 0;
    public const TYPE_CAMPAIGN = 1;

    private static $apiEndPoint;

    public static function buzzCampaignMessage($userId, $campaign, $trigger)
    {
        $messages = '';
        $buzzResult = null;
        try {
            if (self::ignoreMessage($trigger)) {
                $messages = self::getMessage($campaign, $trigger);
                if (empty($messages)) {
                    Log::error('[ChatBuzz][Campaign] text notfound: ' . $trigger);
                }

                $userInfo = User::findOrFail($userId);
                $data = self::getUserInfo($userInfo, []);

                $buzzResult = static::buzzMessage(null, null, $messages, [
                    'trace' => $trigger,
                    'trigger' => $trigger,
                    'campaignId' => $campaign->id,
                    'campaignHash' => $campaign->hash,
                    'mediaType' => $campaign->media_youtube ? CampaignEnum::MEDIA_YOUTUBE : ($campaign->media_instagram ? CampaignEnum::MEDIA_INSTAGRAM : ''),
                    'hasChat' => $campaign->has_chat,
                    'userId' => $userId,
                    'createdBy' => $campaign->operator->hash,
                    'campaignName' => $campaign->title,
                    'campaignSummary' => $campaign->summary,
                    'campaignImage' => StorageS3::getByPath($campaign->main_image),
                    'avatar' => $data['avatar'],
                    'nickname' => $userInfo->nickname ?? '',
                    'instagramName' => $avatarIG->username ?? '',
                    'rawMsgTitle' => $messages,
                    'draftSubmissionDeadline' => ($campaign->workflow) ? $campaign->workflow_draft_submit_date : $campaign->posted_date,
                    'contentRegistrationDeadline' => $campaign->posted_date,
                    'postedStartDate' => $campaign->posted_start_date,
                    'postedDate' => $campaign->posted_date,
                ]);
            }

            $messages = str_replace("{?}", "\r\n", $messages);
        } catch (Exception $ex) {
            Log::error(
                '[ChatBuzz][buzzCampaignMessage] ' . $ex->getMessage(),
                ['$ex' => $ex]
            );
        }

        return [$buzzResult, $messages];
    }

    /**
     * Ignore some case
     */
    public static function ignoreMessage($trigger)
    {
        $ignoreTrigger = [
            'request-revised-influencer-operator-video',
            'request-revised-influencer-advertiser-draft',
            'request-revised-influencer-advertiser-video',
            'operator-confirm-revision-draft-youtube',
            'operator-confirm-revision-video-youtube',
            'advertiser-confirm-revision-draft-blog',
            'advertiser-confirm-revision-draft-youtube',
            'advertiser-confirm-revision-video-youtube',
            'advertiser-absentWorkflowDraft',
            'advertiser-absentWorkflowVideo',
        ];
        if (in_array($trigger, $ignoreTrigger)) {
            return false;
        }

        return true;
    }

    public static function getMessage($campaign, $trigger, array $meta = [])
    {
        switch ($trigger) {
            case 'campaign-selected-new':
            case 'campaign-draft-selected':
            case 'campaign-selected-new-follow-up':
            case 'campaign-draft-selected-follow-up':
                $messages = Helper::trans('chat_buzz.' . $trigger, [
                    'campaign_name' => $campaign->title,
                    'campaign_summary' => $campaign->summary,
                    'campaign_deadline' => $campaign->posted_date,
                    'campaign-draft_deadline' => $campaign->workflow_draft_submit_date,
                    'campaign_requested' => $campaign->requested_date
                ]);
                break;
            case 'campaign-offered-new':
                $basicPoint = $meta['basic_point'] ? $meta['basic_point'] . 'P' : '';
                $additionalPoint = $meta['additional_point'] ? $meta['additional_point'] . 'P' : '';
                $addPoint = $meta['basic_point'] && $meta['additional_point'] ? ' + ' : '';
                $totalPoint = $basicPoint . $addPoint . $additionalPoint;
                $point = '';
                $product = '';
                // If empty disable row
                if (!empty($totalPoint)) {
                    $point = Helper::trans('chat_buzz.' . $trigger . '-point', [
                        'total_point' => $totalPoint,
                    ]);
                }
                // If empty disable row
                if (!empty($meta['product_name'])) {
                    $product = Helper::trans('chat_buzz.' . $trigger . '-product', [
                        'product_name' => $meta['product_name'],
                    ]);
                }

                $messages = Helper::trans('chat_buzz.' . $trigger, [
                    'campaign_name' => $campaign->title,
                    'message_offer' => $meta['message_offer'],
                    'total_point' => $point,
                    'campaign_requested' => $campaign->requested_date,
                    'product_name' => $product
                ]);
                break;
            case 'campaign-absent':
            case 'campaign-best':
            case 'campaign-shipping-apply-new';
                $messages = Helper::trans('chat_buzz.' . $trigger, [
                    'product_name' => $meta['product_name'],
                    'courier_name' => $meta['courier_name'],
                    'invoice_number' => $meta['invoice_number']
                ]);
                break;
            // For Workflow:
            case 'workflow-draft-hurry-up':
            case 'workflow-draft-revised-hurry-up':
            case 'operator-confirm-revision-draft-blog-new':
            case 'operator-confirm-revision-draft-blog-new-follow-up':
            case 'operator-confirm-revision-draft-video-new':
            case 'workflow-video-hurry-up':
            case 'workflow-video-revised-hurry-up':
            case 'request-revised-influencer-operator-draft-new':
            case 'request-revised-influencer-operator-video-new':
            case 'operator-confirm-revision-draft-blog':
            case 'campaign-not-running':
            case 'campaign-absent-draft-no-post':
            case 'workflow-youtube-review':
            case 'campaign-content-hurry-up':
            case 'campaign-content-hurry-up-follow-up':
                $messages = Helper::trans('chat_buzz.' . $trigger, [
                    'campaign_name' => $campaign->title,
                    'deadline' => isset($meta['deadline']) ? $meta['deadline'] : '',
                    'posted_start_date' => isset($meta['postedStartDate']) ? $meta['postedStartDate'] : $campaign->posted_start_date,
                    'posted_date' => $campaign->posted_date,
                    'workflow_video_submit_date' => isset($meta['workflow_video_submit_date']) ? $meta['workflow_video_submit_date'] : $campaign->workflow_video_submit_date,
                    'workflow_video_date' => !empty($meta['workflowVideoDate']) ? $meta['workflowVideoDate'] : $campaign->workflow_video_date,
                    'workflow_draft_date' => $campaign->workflow_draft_date,
                    'draftSubmissionDeadline' => isset($meta['draftSubmissionDeadline']) ? $meta['draftSubmissionDeadline'] : '',
                    'workflowVideoDate' => isset($meta['workflowVideoDate']) ? $meta['workflowVideoDate'] : '',
                    'workflowVideoSubmitDate' => isset($meta['workflowVideoSubmitDate']) ? $meta['workflowVideoSubmitDate'] : '',
                ]);
                break;
            case 'campaign-draft-deadline-extended-new':
            case 'workflow-youtube-deadline-extended':
            case 'campaign-post-deadline-extended-new':
                $messages = Helper::trans('chat_buzz.' . $trigger, [
                    'campaign_name' => $campaign->title,
                    'deadline_extended' => isset($meta['deadline_extended']) ? $meta['deadline_extended'] : '',
                ]);
                break;
            default:
                $messages = '';
        }

        return $messages;
    }

    public static function getUserInfo($userHash, $data = [])
    {
        $userInfo = $userHash;
        if (!is_object($userHash)) {
            $userInfo = User::where('hash', $userHash)->first();
        }

        if (!empty($userInfo)) {
            try {
                if (!isset($data['avatar']) || !isset($data['nickname']) || !isset($data['instagramName'])) {
                    $imgDomain = StorageS3::getByPath('', null, $userInfo->country);
                    $avatar = !empty($userInfo->profile) ? $imgDomain . $userInfo->profile : '';

                    //Get avatar from IG
                    $avatarIG = InstagramUser::select(
                        'instagram_user.profile_picture',
                        'instagram_user.username',
                        'user_media.youtube_user_name'
                    )
                        ->leftJoin(
                            'user_media',
                            'instagram_user.instagram_object',
                            '=',
                            'user_media.instagram_user_object'
                        )
                        ->where('user_media.user_id', $userInfo->id)->first();
                    if (empty($avatar)) {
                        $avatar = isset($avatarIG->profile_picture) ? $avatarIG->profile_picture : '';
                    }
                    $data['avatar'] = $avatar;
                    $data['nickname'] = empty($userInfo->nickname) ? '' : $userInfo->nickname;
                    $data['instagramName'] = isset($avatarIG->username) ? $avatarIG->username : '';
                    $data['youtubeName'] = isset($avatarIG->youtube_user_name) ? $avatarIG->youtube_user_name : '';
                }
            } catch (Exception $ex) {
                Log::error('[ChatBuzz][directBuzz] ' . $ex->getMessage(), ['$ex' => $ex]);
            }
        }

        return $data;
    }

    /**
     * @param string $userHash
     * @param string $countryId
     * @param string $body
     * @param array $data
     */
    public static function buzzMessage($userHash, $countryId, $body, array $data = [])
    {
        app()->configure('chat_buzz');

        $data = self::getUserInfo($userHash, $data);

        //Update campaign has_chat
        if (isset($data['hasChat']) && !$data['hasChat']) {
            $campaign = Campaign::where('hash', $data['campaignHash'])->first();
            if (!empty($campaign)) {
                $campaign->has_chat = 1;
                $campaign->save();
            }
        }

        return static::sendMessage($userHash, $countryId, $body, $data);
    }

    /**
     * Call Chatting Service API to broadcast chat message
     * @param string $userHash
     * @param string $countryId
     * @param string $body
     * @param array $data
     * @return bool|string
     */
    public static function sendMessage($userHash, $countryId, $body, array $data = [])
    {
        $traceLog = $data['trace'] ? "[{$data['trace']}]" : '';
        if (empty($userHash) || empty($countryId) || empty($body)) {
            Log::error('[ChatBuzz] ' . $traceLog . ': missing requirements.');
            return false;
        }

        $market = URI::getInstance()->market;
        if (!Helper::isMarketSelect($market)) {
            Log::error('[ChatBuzz] ' . $traceLog . ': Not support in country:' . $countryId);
            return false;
        }

        if (empty($data['type']) || !in_array($data['type'], [self::TYPE_CAMPAIGN, self::TYPE_SYSTEM])) {
            $data['type'] = self::TYPE_SYSTEM;
        }

        if (!isset($data['avatar']) || !isset($data['nickname']) || !isset($data['instagramName'])) {
            $data = self::getUserInfo($userHash, $data);
        }

        $formData = $data + [
                'userHash' => $userHash,
                'countryId' => $countryId,
                'content' => $body,
            ];

        return self::postToFord('/messages', $formData);
    }

    public static function postToFord($apiPath, $formData = [], $jsonData = [], $requestOpts = [])
    {
        $result = null;
        try {
            app()->configure('services');
            self::$apiEndPoint = config('services.chat_buzz.chatting_service_url') . '/api';
            $uri = self::$apiEndPoint . $apiPath;

            $options = [
                'verify' => false,
                // 'debug'  => true,
            ];
            $formData && $options['form_params'] = $formData;
            if (!empty($jsonData)) {
                $options += [
                    'headers' => [
                        'content-type' => 'application/json',
                    ],
                    'json' => $jsonData,
                ];
            }

            $options += $requestOpts ?: [];

            $client = new Client();
            $res = $client->request('POST', $uri, $options);
            $result = $res->getBody()->getContents();
//            revu_add_log("[ChatBuzz]postToFord: `{$apiPath}` .Result:", ['result' => $result]);
        } catch (Exception $ex) {
            Log::error('[ChatBuzz]PostToFord: ' . $ex->getMessage(), [
                'ex' => $ex,
                'formData' => $formData,
            ]);
        }

        return $result;
    }

    public static function buzzWorkflowMessage($userId, $campaign, $trigger, array $meta = [])
    {
        $messages = '';
        $buzzResult = null;
        try {
            if (self::ignoreMessage($trigger)) {
                $messages = static::getMessage($campaign, $trigger, $meta);
                if (empty($messages)) {
                    Log::error('[ChatBuzz][Workflow] text notfound: ' . $trigger);
                }
                $userInfo = User::findOrFail($userId);
                $userHash = $userInfo->hash;
                $data = self::getUserInfo($userInfo, []);

                $rawMessage = isset($meta['rawMessage']) ? $meta['rawMessage'] : '';
                $buzzResult = static::buzzMessage(null, null, $messages, [
                    'trace' => $trigger,
                    'trigger' => $trigger,
                    'campaignId' => $campaign->id,
                    'campaignHash' => $campaign->hash,
                    'mediaType' => $campaign->media_youtube ? CampaignEnum::MEDIA_YOUTUBE : ($campaign->media_instagram ? CampaignEnum::MEDIA_INSTAGRAM : ''),
                    'hasChat' => $campaign->has_chat,
                    'userId' => $userId,
                    'avatar' => $data['avatar'],
                    'userHash' => ($userHash) ? $userHash : '',
                    'nickname' => isset($userInfo->nickname) ? $userInfo->nickname : '',
                    'instagramName' => isset($avatarIG->username) ? $avatarIG->username : '',
                    'campaignName' => $campaign->title,
                    'campaignSummary' => $campaign->summary,
                    'createdBy' => $campaign->operator->hash,
                    'campaignImage' => StorageS3::getByPath($campaign->main_image),
                    'deadline' => isset($meta['deadline_extended']) ? $meta['deadline_extended'] : '',
                    'deadline_count' => isset($meta['deadline_count']) ? $meta['deadline_count'] : '',
                    'modifyRequest' => isset($meta['modifyRequest']) ? $meta['modifyRequest'] : '',
                    'rawMsgTitle' => $messages,
                    'rawMsgBody' => isset($rawMessage['rawMsgBody']) ? $rawMessage['rawMsgBody'] : '',
                    'rawMsgOverall' => isset($rawMessage['rawMsgOverall']) ? $rawMessage['rawMsgOverall'] : '',
                    'productName' => isset($meta['product_name']) ? $meta['product_name'] : '',
                    'offerMessage' => isset($meta['message_offer']) ? $meta['message_offer'] : '',
                    'basicPoint' => isset($meta['basic_point']) ? $meta['basic_point'] : 0,
                    'additionalPoint' => isset($meta['additional_point']) ? $meta['additional_point'] : 0,
                    'recruitmentDeadline' => $campaign->requested_date,
                    'contentRegistrationDeadline' => $campaign->posted_date,
                    'draftSubmissionDeadline' => isset($meta['draftSubmissionDeadline']) ? $meta['draftSubmissionDeadline'] : $campaign->workflow_draft_date,
                    'courierName' => isset($meta['courier_name']) ? $meta['courier_name'] : '',
                    'invoiceNumber' => isset($meta['invoice_number']) ? $meta['invoice_number'] : '',
                    'postedStartDate' => isset($meta['postedStartDate']) ? $meta['postedStartDate'] : $campaign->posted_start_date,
                    'postedDate' => isset($meta['postedDate']) ? $meta['postedDate'] : $campaign->posted_date,
                    'workflowVideoSubmitDate' => isset($meta['workflowVideoSubmitDate']) ? $meta['workflowVideoSubmitDate'] : $campaign->workflow_video_submit_date,
                    'workflowVideoDate' => !empty($meta['workflowVideoDate']) ? $meta['workflowVideoDate'] : $campaign->workflow_video_date,
                    'extendedDate' => isset($meta['deadline_extended']) ? $meta['deadline_extended'] : '',
                    'totalPoint' => isset($meta['total_point']) ? $meta['total_point'] : 0,
                ]);
            }

            $messages = str_replace("{?}", "\r\n", $messages);
        } catch (Exception $ex) {
            Log::error(
                '[ChatBuzz][buzzCampaignMessage] ' . $ex->getMessage(),
                ['$ex' => $ex]
            );
        }

        return [$buzzResult, $messages];
    }

    /* RSA-521 */

    /**
     * Ignore some case
     */
    public static function ignoreMessageFollow($trigger)
    {
        $ignoreTrigger = [
            'campaign-selected-new-follow-up',
            'campaign-draft-selected-follow-up',
            'operator-confirm-revision-draft-blog-new-follow-up',
            'campaign-content-hurry-up-follow-up'
        ];
        if (in_array($trigger, $ignoreTrigger)) {
            return false;
        }

        return true;
    }

    public static function getNotificationData($buzzResult)
    {
        if (!$buzzResult) {
            Log::warning('[ChatBuzz] no Response data from chatbuzz.');
            return [];
        }

        $buzzResult = is_array($buzzResult) ? $buzzResult : json_decode($buzzResult, true);
        $buzzResult = Arr::get($buzzResult, 'message', []);
        return [
            'conversationId' => Arr::get($buzzResult, 'conversationId'),
            'isEnableReply' => Arr::get($buzzResult, 'isEnableReply'),
            'campaignTitle' => Arr::get($buzzResult, 'campaignTitle'),
            'campaignImage' => Arr::get($buzzResult, 'campaignImage'),
            'campaignHash' => Arr::get($buzzResult, 'campaignHash'),
            'pushType' => 'chatmessage',
        ];
    }
}
