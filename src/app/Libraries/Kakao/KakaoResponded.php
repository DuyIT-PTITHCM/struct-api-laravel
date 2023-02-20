<?php

namespace App\Libraries\Kakao;

use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Kakao Response Request
 */
class KakaoResponded
{
    /**
     * Check atk messages real sent by MTS
     *
     * @param array $data
     * @return array|null
     */
    public static function requestAtkResponse($data)
    {
        try {
            return Common::pushToKakao(Common::PATH_CHECK_ATK, $data);
        } catch (Exception $ex) {
            Log::error('[KakaoAtk][requestAtkResponse]' . $ex->getMessage(), [
                'data' => $data,
                'ex' => $ex
            ]);
            return [];
        }
    }

    /**
     * Check sms messages real sent by MTS
     *
     * @param array $data
     * @return array|null
     */
    public static function requestSmsResponse($data)
    {
        try {
            return Common::pushToKakao(Common::PATH_CHECK_SMS, $data);
        } catch (Exception $ex) {
            Log::error('[KakaoSms][requestSmsResponse]' . $ex->getMessage(), [
                'data' => $data,
                'ex' => $ex
            ]);
            return [];
        }
    }
}
