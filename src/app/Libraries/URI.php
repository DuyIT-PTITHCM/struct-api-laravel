<?php

namespace App\Libraries;

use App\Enums\MarketEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class URI
{
    use Singleton;

    public $market;
    public $host;
    public $namespace;
    public $namespaceStr;
    public $version;
    public $country;
    public $locale = 'ko';
    public $user = null;

    public function __construct()
    {
        $this->createFromRequest();
    }

    /**
     * @return void
     */
    private function createFromRequest()
    {
        // EX URL : https://api.search-api/v1.0/campaigns
        $hostSegments = explode('.', Request::server('HTTP_HOST'));
        $namespace = array_shift($hostSegments);
        $this->namespace = in_array(
            $namespace,
            config('uri.namespaces')
        ) ? $namespace : config('uri.defaults.namespace');
        $this->namespaceStr = 'App\\Http\\Controllers\\' . ucfirst($this->namespace);
        $this->host = implode('.', $hostSegments);
        $version = Request::segment(1);
        if (in_array($version, config('uri.versions'))) {
            $this->version = $version;
        } else {
            $this->version = 'v1.0';
        }

        $this->setCountry(Request::input('country', env('COUNTRY')));
        $this->market = Request::input('market', MarketEnum::SELECT);
    }

    public function setCountry($countryCode)
    {
        if (!in_array($countryCode, Helper::getCountriesAvailable())) {
            abort(400, "Country {$countryCode} is not available.");
        }
        $this->country = $countryCode;

        $this->setDBConnectionName();
        $this->setMailer();
        $this->getLocale(true);
    }

    public function setDBConnectionName()
    {
        $countryCode = strtolower($this->country);
        $connection = "web_{$countryCode}";
        if (!empty($connection)) {
            config(['database.default' => $connection]);

            // set timezone per country
            $timezone = env(strtoupper($this->country) . '_APP_TIMEZONE', env('APP_TIMEZONE'));
            date_default_timezone_set($timezone);

//            config(['app.timezone' => $timezone]);
        }
    }

    public function setMailer()
    {
        $country = strtoupper($this->country);
        // override mail from
        config([
            'mail.from' => [
                'address' => env("{$country}_MAIL_FROM_ADDRESS", env('MAIL_FROM_ADDRESS')),
                'name' => env("{$country}_MAIL_FROM_NAME", env('MAIL_FROM_NAME')),
            ]
        ]);

//        Mail::alwaysFrom(
//            env("{$country}_MAIL_FROM_ADDRESS", env('MAIL_FROM_ADDRESS')),
//            env("{$country}_MAIL_FROM_NAME", env('MAIL_FROM_NAME'))
//        );
    }

    public function getLocale($setConfig = false): string
    {
        switch ($this->country) {
            case 'vn':
                $locale = 'vi';
                break;
            case 'tw':
                $locale = 'zh';
                break;
            case 'kr':
                $locale = 'ko';
                break;
            default:
                $locale = $this->country;
                break;
        }

        if ($setConfig) {
            config(['app.locale' => $locale]);
        }
        $this->locale = $locale;
        return $locale;
    }

    public function getMarket()
    {
        return $this->market ?: MarketEnum::SELECT;
    }

    public function setMarket($model)
    {
        $this->market = is_string($model) ? $model : (isset($model->market) ? $model->market : MarketEnum::SELECT);
        if (!in_array($this->market, Helper::getMarketsAvailable())) {
            abort(400, "Market {$this->market} is not available.");
        }
    }

    public function getNaverBlogConnection($isDBLog = false)
    {
        $connection = $isDBLog ? 'naver_blog_log' : 'naver_blog';
        return DB::connection($connection);
    }

    public function setUser($user)
    {
        $this->user = $user;
        $this->setCountry($user->country);
        $this->setMarket($user->market);
    }

    public function getIgnoreModelScopeMarket()
    {
        return [];
    }
}
