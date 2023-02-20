<?php

namespace App\Libraries;

use App\Mail\CampaignOffer;
use App\Mail\CampaignOfferAgency;
use App\Models\AgencyManager;
use App\Models\UserAgency;
use Exception;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class Mailer
{
    public static function campaignOffered($user, $campaign, $meta = [])
    {
        $sns = $campaign->getSns();

        if ($user->isBelongToAgency($sns)) {
            $messageOffer = Helper::trans('emails.campaign-offered-agency-message', [
                "campaign_name" => $campaign->title,
            ]);

            self::campaignOfferedToAgency($user, $campaign, $messageOffer);
        } else {
            if (in_array($campaign->country, ['tw'])) {
                self::sendMailTo($user->email, new CampaignOffer($user, $campaign, $meta));
            }
        }
    }

    public static function campaignOfferedToAgency($user, $campaign, $offerMessage)
    {
        $sns = $campaign->getSns();
        $userAgency = UserAgency::where('user_id', $user->id)->first();
        if (empty($userAgency)) {
            return false;
        }

        $agencyId = "agency_{$sns}_id";
        $agencyManagerId = "agency_{$sns}_manager_id";
        $agencyName = "agency_{$sns}_name";

        if (
            $userAgency->{$agencyId} > 0
            && !empty($userAgency->{$agencyManagerId})
        ) {
            $agencyManagers = AgencyManager::whereIn('id', $userAgency->{$agencyManagerId})->get();
            foreach ($agencyManagers as $agencyManager) {
                try {
                    self::sendMailTo(
                        $agencyManager->email,
                        new CampaignOfferAgency(
                            $user, $campaign, $offerMessage, $userAgency->{$agencyName},
                            $agencyManagers
                        )
                    );
                } catch (Exception $ex) {
                    print $ex->getMessage();
                }
            }
        } else {
            return false;
        }
    }

    /**
     * @param string|object $emailTo
     * @param Mailable $mailTemplate
     * @return void
     */
    public static function sendMailTo($emailTo, Mailable $mailTemplate)
    {
        // if $emailTo is object => make sure we need to have {email: 'xx', name: 'xx'}

        Mail::to($emailTo)
            ->send($mailTemplate);
    }
}
