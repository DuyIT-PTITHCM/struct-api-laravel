<?php

namespace App\Libraries;

use Exception;
use Illuminate\Support\Facades\Log;

class Core
{
    /**
     * render Routes files
     *
     * @param string $folder folder under routes/*
     * @param object $router
     * @return void
     */
    public static function renderRoutes($folder, $router)
    {
        foreach (glob(app()->basePath() . "/routes/{$folder}/*.php") as $filename) {
            require $filename;
        }
    }

    public static function execCurl($headerOption, $url, $payload)
    {
        $header = array();
        $header[] = 'Content-type: application/json';
        $header = array_merge($header, $headerOption);
        $crl = curl_init();
        curl_setopt($crl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($crl, CURLOPT_POST, true);
        curl_setopt($crl, CURLOPT_URL, $url);
        curl_setopt($crl, CURLOPT_POSTFIELDS, json_encode($payload));

        curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($crl, CURLOPT_SSL_VERIFYHOST, false); //should be off on production
        curl_setopt($crl, CURLOPT_SSL_VERIFYPEER, false); //shoule be off on production
        curl_setopt($crl, CURLOPT_TIMEOUT_MS, 10000);

        $rest = curl_exec($crl);
        if ($rest === false) {
            return curl_error($crl);
        }
        curl_close($crl);

        return json_decode($rest, true);
    }

    /**
     * Custom Response HTTP
     *
     * @param array $data response data
     * @param string $type type of response ['success','error', etc]
     * @param string $message response message
     * @param bool $is_array if set true, array return will given
     * @param int $code header code of response
     * @return mixed array|json
     */
    public static function setResponse($data = [], $message = "Successful.", $type = 'success', $isArray = false, $code = null)
    {
        switch ($type) {
            case 'not_found':
                $status = $type;
                $body = $data;
                $code = 404;
                $message = "Not Found";
                break;
            case 'error':
                $status = "error";
                $body = 'error_info';
                $code = $code ?? 400;
                break;
            default:
                $status = "success";
                $body = 'data';
                $code = $code ?? 200;
        }

        if (is_array($data) && !empty($data["error"])) {
            $response = $data;
            $code = 400;
        } else {
            $response = [
                $status => $message,
            ];

            if (method_exists($data, 'toArray')) {
                $toArray = $data->toArray();
                if (!empty($toArray['data'])) {
                    $response += $toArray;
                } else {
                    $response[$body] = $data;
                }
            } elseif (is_object($data) && property_exists($data, 'data')) {
                $response += (array)$data->data;
            } elseif(is_array($data)) {
                if(!isset($data['data'])) {
                    $response['data'] = $data;
                } else {
                    $response = $data;
                }
            } else {
                $response[$body] = $data;
            }
        }

        if ($isArray) {
            if(is_array($data) && !isset($data['data'])) {
                $response['data'] = $data;
            } elseif (is_object($data) && !property_exists($data, 'data')) {
                $response['data'] = $data;
            } else {
                $response = $data;
            }
            return response()->json($response, $code);
        }

        return response()->json($response, $code);
    }

    /**
     * custom log for loops
     *
     * @param string $type type of log
     * @param string $message log message
     * @param bool $force force create log avoid config
     * @return string error ref.
     */
    public function log($type = 'debug', $message = null, $force = false)
    {
        $errorRef = $this->generateRandomString(7);
        try {
//            if ( config( 'appindex.logs.' . $type . '_active' ) || $force ) {
            Log::{$type}("[$errorRef] $message");
//            }
        } catch (Exception $e) {
            Log::warning("[$errorRef]::log($type, $message) : " . $e->getMessage());
        }
        return $errorRef;
    }

    /**
     * generate Random String
     *
     * @param int $length max character length
     * @return string
     */
    public static function generateRandomString($length = 5)
    {
        $characters = config('erm.error.random_string', 'ABCDEFGHJKLMNPQRTUVWXYZ');
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
