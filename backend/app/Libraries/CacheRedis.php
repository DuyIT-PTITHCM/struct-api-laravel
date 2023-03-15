<?php

namespace App\Libraries;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Request;

class CacheRedis
{
    private static int $expire = 60; // 60 Minutes

    public static function checkCacheConnection() {
        return !empty(env('CACHE_ENABLE', true)) && empty(Request::input('dump_query'));
    }

    public static function set($key, $value, $expireInMinute = null)
    {
        if(!self::checkCacheConnection()){
            return;
        }
        Redis::set($key, json_encode($value));

        Redis::expire($key, ($expireInMinute ?? self::$expire) * 60);
    }

    public static function getByWildcard(string $pattern)
    {
        if(!self::checkCacheConnection()){
            return [];
        }
        $redisPrefix = env('REDIS_PREFIX');
        $keys = Redis::keys($pattern . '*');
        $data = [];
        foreach ($keys as $key) {
            $cacheKey = str_replace($redisPrefix, '', $key);
            $data[$cacheKey] = self::get($cacheKey);
        }
        return $data;
    }

    public static function get($key)
    {
        if(!self::checkCacheConnection()){
            return null;
        }
        // Redis::exists($key)
        $data = Redis::get($key);

        if (!empty($data)) {
            $data = json_decode($data);
        }

        return $data ?? null;
    }

    public static function deleteByWildcard(string $pattern)
    {
        if(!self::checkCacheConnection()){
            return [];
        }
        $redisPrefix = env('REDIS_PREFIX');
        $keys = Redis::keys($pattern . '*');
        foreach ($keys as $key) {
            $cacheKey = str_replace($redisPrefix, '', $key);
            self::delete($cacheKey);
        }
        return $keys;
    }

    public static function delete($key)
    {
        if(!self::checkCacheConnection()){
            return;
        }
        Redis::del($key);
    }

    public static function deleteByUserHash(string $userHash)
    {
        $res = self::deleteByWildcard("middleware:v1.0_influencer_curation");
        $res += self::deleteByWildcard("InfluencerProfile:User:pnu:{$userHash}");
        return $res;
    }

}
