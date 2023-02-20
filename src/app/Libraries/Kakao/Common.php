<?php

namespace App\Libraries\Kakao;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class Common
{
    public const PATH_CHECK_SMS = 'rspns/sms/rspnsMessages';
    public const PATH_SEND_SMS = 'sndng/sms/sendMessage';
    public const PATH_SEND_MULTI_SMS = 'sndng/sms/sendMessages';
    public const PATH_CHECK_ATK = 'rspns/atk/rspnsMessages';
    public const PATH_SEND_ATK = 'sndng/atk/sendMessage';
    public const PATH_SEND_MULTI_ATK = 'sndng/atk/sendMessages';

    /**
     * Send request to MTSCO API
     *
     * @param string $apiPath
     * @param array $data
     * @param string $method
     * @return array|null
     */
    public static function pushToKakao($apiPath, $data = [], $method = 'POST')
    {
        app()->configure('services');
        $kakaoConfigs = config('services.kakao');
        $endPoint = $kakaoConfigs['api_host'] . "/{$apiPath}";

        if (empty($kakaoConfigs['auth_code'])) {
            Log::error('[Kakao][pushToKakao] missing auth_code.');
            return null;
        }

        $bodyData = [
            'auth_code' => $kakaoConfigs['auth_code'],
        ];
        $bodyData = array_merge($bodyData, $data);

        $client = new Client();
        $requestOpts = [
            'headers' => [
                'content-type' => 'application/json',
            ],
            'json' => $bodyData,
        ];
        try {
            $response = $client->request($method, $endPoint, $requestOpts);
            $mtsResponse = $response->getBody()->getContents();
            $result = static::parseResult($mtsResponse);
            // Log::addInfo('[Kakao] MST API Response', ['result' => $result]);
            return $result;
        } catch (GuzzleException $e) {
            Log::error('[Kakao][Guzzle] Request failed:' . $e->getMessage());
            return null;
        }
    }

    protected static function parseResult($rawResponse)
    {
        if (empty($rawResponse)) {
            return [];
        }

        return json_decode($rawResponse, true);
    }

}
