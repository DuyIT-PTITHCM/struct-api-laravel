<?php

namespace App\Libraries;

use App\Enums\MarketEnum;
use App\Models\Role;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Helper
{

    /**
     * @param $name
     * @param null $value
     */
    public static function env($name, $value = null)
    {
        putenv("$name=$value");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }

    /**
     * @return string
     */
    public static function generateHash()
    {
        return str_replace('.', '', uniqid(null, true));
    }

    /**
     * @param $str
     * @return string
     */
    public static function htmlDecode($str)
    {
        return htmlspecialchars_decode(html_entity_decode($str), ENT_QUOTES);
    }

    /**
     * Remove all non-numeric chars
     * @param string $string
     * @return string|string[]|null
     */
    public static function removeNonNumeric($string)
    {
        return preg_replace('~\D~', '', $string);
    }

    /**
     * @return string
     */
    public static function hash()
    {
        return str_replace('.', '', uniqid(null, true));
    }

    /**
     * @param $str
     * @return bool|string
     */
    public static function password($str)
    {
        return password_hash($str, PASSWORD_BCRYPT);
    }

    public static function getCountriesAvailable()
    {
        return config('uri.countries', ['kr', 'tw']);
    }

    public static function getMarketsAvailable()
    {
        return config('uri.markets', ['select']);
    }

    public static function getRemoveTriggerCroneleven()
    {
        $trigger = [
            'campaign-not-running',
            'workflow-draft-hurry-up',
            'workflow-draft-revised-hurry-up',
            'workflow-video-hurry-up',
            'workflow-video-revised-hurry-up',
            'campaign-content-hurry-up',
            'campaign-content-hurry-up-follow-up',
        ];

        return $trigger;
    }

    public static function isRoleManager($roleName)
    {
        return in_array($roleName, [Role::ROLE_MANAGER, Role::ROLE_SUPER_MANAGER]);
    }

    public static function isRolePartner($roleName)
    {
        return in_array($roleName, [Role::ROLE_PARTNER]);
    }

    public static function isMarketSelect($market)
    {
        if (is_object($market) && isset($market->market)) {
            $market = $market->market;
        }

        return $market == MarketEnum::SELECT;
    }

    public static function checkProfileFanpage(
        $facebook_user_object = '',
        $facebook_page_object = '',
        $facebook_profile_object = ''
    ) {
        $profile = false;
        // Priority is fanpage when facebook_user_object and facebook_page_object not null
        if ($facebook_user_object && $facebook_page_object) {
            $profile = false;
        } elseif ($facebook_user_object && !$facebook_page_object) {
            $profile = true;
        } elseif (!$facebook_user_object && $facebook_page_object) {
            $profile = false;
        }
        return $profile;
    }

    public static function isDevelopment()
    {
        return env('APP_ENV') == 'development';
    }

    public static function isTesting()
    {
        return env('APP_ENV') == 'testing';
    }

    public static function isStaging()
    {
        return env('APP_ENV') == 'staging';
    }

    public static function isProduction()
    {
        return env('APP_ENV') == 'production';
    }

    public static function getVersionApiRecentReview($countryCode = null)
    {
        if (empty($countryCode)) {
            $countryCode = URI::getInstance()->country;
        }

        return env(strtoupper($countryCode) . '_VERSION_API', 'v2');
    }

    public static function getInstagramFollowerRange($follower = 0)
    {
        if ($follower <= 10000) {
            return [0, 10000];
        }

        if ($follower >= 10001 && $follower <= 50000) {
            return [10001, 50000];
        }

        if ($follower >= 50001 && $follower <= 100000) {
            return [50001, 100000];
        }

        if ($follower >= 100001) {
            return [100001];
        }
    }

    public static function getYoutubeSubscriberRange($subscriber = 0)
    {
        if ($subscriber <= 10000) {
            return [0, 10000];
        }

        if ($subscriber >= 10001 && $subscriber <= 50000) {
            return [10001, 50000];
        }

        if ($subscriber >= 50001 && $subscriber <= 100000) {
            return [50001, 100000];
        }

        if ($subscriber >= 100001) {
            return [100001];
        }
    }

    public static function getBlogVisitorRange($visitor = 0)
    {
        if ($visitor <= 1000) {
            return [0, 1000];
        }

        if ($visitor >= 1001 && $visitor <= 5000) {
            return [1001, 5000];
        }

        if ($visitor >= 5001 && $visitor <= 10000) {
            return [5001, 10000];
        }

        if ($visitor >= 10001 && $visitor <= 20000) {
            return [10001, 20000];
        }

        if ($visitor >= 20001) {
            return [20001];
        }
    }

    public static function getHashtagsFromString($str)
    {
        if (empty($str)) {
            return [];
        }
        $regex = '/#[^\s!@#$%^&*()=+.\/,\[{\]};:\'"?><]+/';
        preg_match_all($regex, $str, $matches, PREG_SET_ORDER, 0);

        return $matches;
    }

    public static function getMediaTypeByCampaign($campaign)
    {
        $mediaType = '';
        if (!empty($campaign->media_blog) && $campaign->media_blog == 1) {
            $mediaType = 'blog';
        }
        if (!empty($campaign->media_youtube) && $campaign->media_youtube == 1) {
            $mediaType = 'youtube';
        }
        if (!empty($campaign->media_instagram) && $campaign->media_instagram == 1) {
            $mediaType = 'instagram';
        }
        if (!empty($campaign->media_tiktok) && $campaign->media_tiktok == 1) {
            $mediaType = 'tiktok';
        }
        if (!empty($campaign->media_facebook) && $campaign->media_facebook == 1) {
            if (!empty($mediaType)) {
                $mediaType = ($mediaType == 'blog') ? $mediaType . '_facebook' : 'facebook_' . $mediaType;
            } else {
                $mediaType = 'facebook';
            }
        }

        return $mediaType;
    }

    public static function getDeepLinkByType($type, $linkUrl = '', $linkTitle = '')
    {
        $configDeepLink = config('uri.deep_link');
        $deepLink = '';

        switch ($type) {
            case 'url' :
                $deepLink = $configDeepLink['url'] . 'link_url=' . $linkUrl;
                if (!empty($linkTitle)) {
                    $deepLink .= '&link_title=' . $linkTitle;
                }
                break;
            case 'speciallist':
                $deepLink = $configDeepLink['speciallist'];
                break;
            case 'home':
                $deepLink = $configDeepLink['app_run'];
                break;
            default:
                $deepLink = ($configDeepLink[$type] ?? '') . $linkUrl;
                break;
        }

        return self::createDeepLink($deepLink);
    }

    public static function createDeepLink($deepLink)
    {
        $countryCode = URI::getInstance()->country;
        $path = 'deep-link?url=' . urlencode($deepLink);

        return self::getUrlWebview($countryCode, $path);
    }

    public static function getUrlWebview($countryCode, $path = '')
    {
        if ($path && !Str::startsWith($path, '/')) {
            $path = '/' . $path;
        }

        $porscheUrl = str_replace('{countryCode}', $countryCode, env('PORSCHE_WEBVIEW_URL'));

        return $porscheUrl . $path;
    }

    public static function getShortLinkFirebase($hash = '', $options = [])
    {
        $shortLink = '';
        $country_code = URI::getInstance()->country;
        app()->configure('services.firebase');
        $config = config('services.firebase');
        $endpoint = $config["shortlink"];

        $domainUriPrefix = Arr::get($options, 'domainUriPrefix', $config['domainUriPrefix']);

        $path = Arr::get($options, 'urlPath', 'campaign');
        $urlSuffix = Arr::get($options, 'urlSuffix');

        $path .= $hash ? "/{$hash}" : '';
        $linkUrl = self::getUrlWebview($country_code, $path);
        $linkUrl .= $urlSuffix ? "/{$urlSuffix}" : ''; // Eg: campaign/campaign-hash/posting

        $payload = [
            "dynamicLinkInfo" => [
                'domainUriPrefix' => $domainUriPrefix,
                'link' => $linkUrl,
                'androidInfo' => [
                    'androidPackageName' => $config['androidPackageName']
                ],
                'iosInfo' => [
                    'iosBundleId' => $config['iosBundleId'],
                    'iosAppStoreId' => $config['iosAppStoreId'],
                ]
            ]
        ];
        $result = Core::execCurl([], $endpoint, $payload);
        if (isset($result['shortLink'])) {
            $shortLink = $result['shortLink'];
        }

        return $shortLink;
    }

    public static function getPorscheUrl($countryCode, $path = '')
    {
        if ($path && !Str::startsWith($path, '/')) {
            $path = '/' . $path;
        }

        $porscheUrl = str_replace('{countryCode}', $countryCode, env('PORSCHE_URL'));

        return $porscheUrl . $path;
    }

    public static function trans($id = null, $parameters = [], $locale = null)
    {
        if (empty($id)) {
            return '';
        }

        $locale = $locale ?: URI::getInstance()->locale;
        return trans($id, $parameters, $locale);
    }

    public static function cutStringUsername($value, $limit = 2){
        if (empty($value)) {
            return $value;
        }

        $len = mb_strlen($value) - 2;
        if($len <= 0) {
            return $value;
        }

        return Str::substr($value, 0, $limit) . implode('', array_fill(0, $len, '*'));
    }
}
