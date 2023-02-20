<?php

namespace App\Libraries;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageS3
{
    public static $S3_TO_CLOUDFRONT_FIND = [
        "https://revu-storage-dev.s3.amazonaws.com",
        "https://revu-storage-dev.s3.ap-southeast-1.amazonaws.com",
        "https://ysg-dev2.s3.amazonaws.com",
        "https://revu-storage-staging.s3.amazonaws.com",
        "https://ysg-th.s3.amazonaws.com",
        "https://revu-storage-vn.s3.amazonaws.com",
        "https://revu-storage-vn.s3.ap-southeast-1.amazonaws.com",
        "https://revu-storage-id.s3.amazonaws.com",
        "https://revu-storage-tw.s3.amazonaws.com",
        "https://revu-storage-ph.s3.amazonaws.com",
        "https://revu-storage-kr.s3.amazonaws.com",
        "https://revu-storage-kr.s3.ap-northeast-2.amazonaws.com"
    ];
    public static $S3_TO_CLOUDFRONT_REPLACE = [
        "https://d2y5m24zvgyat8.cloudfront.net",
        "https://d2y5m24zvgyat8.cloudfront.net",
        "https://dgmgpj45vtyhg.cloudfront.net",
        "https://d2b96ph526kqxs.cloudfront.net",
        "https://d2y641ysmlbyo9.cloudfront.net",
        "https://d1hv1msmiluehs.cloudfront.net",
        "https://d1hv1msmiluehs.cloudfront.net",
        "https://d1atfr6qmrlxae.cloudfront.net",
        "https://d394qflqg7bgkx.cloudfront.net",
        "https://d2d0rn19wwcny1.cloudfront.net",
        "https://d1uuuninuo43kf.cloudfront.net",
        "https://d1uuuninuo43kf.cloudfront.net"
    ];

    public static function getConnection()
    {
        $countryCode = Str::lower(URI::getInstance()->country);
        return 's3_' . $countryCode;
    }

    public static function get()
    {
        return Storage::disk(self::getConnection());
    }

    public static function getByPath($path = '', $country = null): string
    {
        if ($path && !Str::startsWith($path, '/')) {
            $path = '/' . $path;
        }

        return self::getS3Url($country) . $path;
    }

    public static function getS3Url($country = null): string
    {
        $s3Bucket = env(Str::upper($country ?: URI::getInstance()->country) . '_S3_BUCKET', env('S3_BUCKET'));

        return "https://{$s3Bucket}.s3.amazonaws.com";
    }

    public static function getCloudFrontUrl($country = null)
    {
        return env('CLOUD_FRONT_URL_' . Str::upper($country ?: URI::getInstance()->country), env('CLOUD_FRONT_URL'));
    }

    public static function replaceS3ToCloudFront($url)
    {
        if (empty($url)) {
            return $url;
        }

        // Check if this url is not absolute link
        if (!Str::startsWith($url, 'http')) {
            if (!Str::startsWith($url, '/')) {
                $url = '/' . $url;
            }
            return self::getCloudFrontUrl() . $url;
        }

        return Str::replace(self::$S3_TO_CLOUDFRONT_FIND, self::$S3_TO_CLOUDFRONT_REPLACE, $url);;
    }

    public static function replaceS3ToCloudFrontRawSelect($field, $asField)
    {
        $rawSelect = $field;
        foreach (self::$S3_TO_CLOUDFRONT_FIND as $index => $s3Url) {
            $cloudFrontUrl = self::$S3_TO_CLOUDFRONT_REPLACE[$index];
            if ($index == 0) {
                $rawSelect = "REPLACE({$field}, '{$s3Url}', '{$cloudFrontUrl}')";
                continue;
            }

            $rawSelect = "REPLACE({$rawSelect}, '{$s3Url}', '{$cloudFrontUrl}')";
        }
        return $rawSelect . " AS {$asField}";
    }
}
