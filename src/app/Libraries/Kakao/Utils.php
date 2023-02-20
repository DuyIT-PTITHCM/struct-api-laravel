<?php

namespace App\Libraries\Kakao;

use App\Libraries\Helper;
use App\Repositories\Api\UserSettingRepository;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * Kakao helpers
 */
class Utils
{
    /**
     * Convert from global phone number (+82) to internal phone number(start with 0)
     *
     * @param string $globalPhoneNo
     * @return string
     * @throws Exception
     */
    public static function convertToInternalNumber($globalPhoneNo)
    {
        $cleanInputPhone = self::removeNonNumeric($globalPhoneNo);
        if (empty($cleanInputPhone) || strpos($cleanInputPhone, '0') === 0) {
            return $cleanInputPhone; // internal format already
        }

        $callingCode = self::getNationCodeFromPhone($globalPhoneNo);

        $callingCode = self::removeNonNumeric($callingCode);
        $internalPhoneNo = substr_replace($cleanInputPhone, '', 0, strlen($callingCode));

        return strpos($internalPhoneNo, '0') !== 0 ? "0{$internalPhoneNo}" : $internalPhoneNo;
    }

    /**
     * Remove all non-numeric chars
     *
     * @param string $raw
     * @return string|null
     */
    public static function removeNonNumeric($raw)
    {
        return Helper::removeNonNumeric($raw);
    }

    /**
     * @param $globalPhoneNo
     * @return mixed|null
     * @throws Exception
     */
    public static function getNationCodeFromPhone($globalPhoneNo)
    {
        app()->configure('services');
        $callingCodes = config('services.sms.country_calling_codes');
        $callingCode = null;

        $cleanInputPhone = self::removeNonNumeric($globalPhoneNo);
        if (empty($cleanInputPhone) || strpos($cleanInputPhone, '0') === 0) {
            return null; // empty or internal format, can not detect
        }

        foreach ($callingCodes as $code) {
            $cleanCode = self::removeNonNumeric($code);
            if (strpos($cleanInputPhone, $cleanCode) === 0) {
                $callingCode = $cleanCode;
                break;
            }
        }

        if (!$callingCode) {
            Log::error("Your phone number[$cleanInputPhone] is not supported in nation code list.");
        }

        return $callingCode;
    }

    /**
     * @param int $userId
     * @param string $trigger
     * @return mixed
     */
    public static function checkUserAvailable($userId, $trigger)
    {
        $notificationMap = [
            'inquiry-answered-new' => 'service_notification',
            'campaign-offered-new' => 'event_notification',
            'default' => 'campaign_notification',
        ];
        $type = Arr::get($notificationMap, $trigger, $notificationMap['default']);

        $userSettingRepo = new UserSettingRepository();
        return !$userSettingRepo->isTurnOnNotification($userId, $type);
    }
}
