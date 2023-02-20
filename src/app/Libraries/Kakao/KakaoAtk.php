<?php

namespace App\Libraries\Kakao;

use App\Models\SmsHistory;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * Kakao Alim Talk
 */
class KakaoAtk
{
    /**
     * Send single message
     *
     * @param array $data
     * @return array|null
     */
    public static function sendMessage($data)
    {
        try {
            $errors = self::validateData($data);
            if ($errors) {
                throw new Exception('[KakaoAtk][sendMessage] data invalid.');
            }

            return Common::pushToKakao(Common::PATH_SEND_ATK, $data);
        } catch (Exception $ex) {
            Log::error('[KakaoAtk][sendMessage]' . $ex->getMessage(), [
                'sendData' => $data,
                'ex' => $ex
            ]);
        }

        return [];
    }

    protected static function validateData($data)
    {
        $requiredFields = ['sender_key', 'callback_number', 'phone_number', 'message', 'template_code'];
        $errorFields = [];
        foreach ($data as $key => $value) {
            if (empty($value) && in_array($key, $requiredFields)) {
                $errorFields[$key] = "${$key} is required.";
            }
            // other validation here
        }

        return $errorFields;
    }

    /**
     * Send multiple messages
     *
     * @param array $data
     * @return array|null
     */
    public static function sendMessages($data)
    {
        try {
            foreach ($data as $index => $row) {
                $errors = self::validateData($row);
                if (empty($errors)) {
                    continue;
                }

                Log::error("[KakaoAtk][sendMessages] data invalid: item[{$index}]", [
                    'sendData' => $data,
                    'errors' => $errors
                ]);
                unset($data[$index]);
            }

            if (empty($data)) {
                return null;
            }

            return Common::pushToKakao(Common::PATH_SEND_MULTI_ATK, $data);
        } catch (Exception $ex) {
            Log::error('[KakaoAtk][sendMessages]' . $ex->getMessage(), [
                'sendData' => $data,
                'ex' => $ex
            ]);
        }
    }

    public static function saveHistory($data)
    {
        try {
            $userId = Arr::get($data, 'user_id');
            $templateCode = Arr::get($data, 'template_code');
            if (empty($userId) || empty($templateCode)) {
                return null; // nothing to save
            }

            $result = Arr::get($data, 'result');
            $result = is_scalar($result) ? $result : json_encode($result);

            SmsHistory::create([
                'provider' => SmsHistory::PROVIDER_KAKAO,
                'type' => SmsHistory::TYPE_ATK,
                'user_id' => $userId,
                'template_code' => $templateCode,
                'campaign_id' => Arr::get($data, 'campaign_id'),
                'state' => Arr::get($data, 'state'),
                'phone_number' => Arr::get($data, 'phone_number'),
                'result' => $result,
            ]);
        } catch (Exception $ex) {
            Log::error('[KakaoAtk][saveHistory] failed.' . $ex->getMessage(), ['ex' => $ex]);
        }
    }
}
