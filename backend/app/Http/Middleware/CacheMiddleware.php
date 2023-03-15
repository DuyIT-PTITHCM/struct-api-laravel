<?php

namespace App\Http\Middleware;

use App\Libraries\CacheRedis;
use App\Libraries\Helper;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class CacheMiddleware
{
    private int $cacheExpired = 60; // cached data for 60 mins

    public function handle(Request $request, Closure $next)
    {
        if (!in_array($request->method(), ['GET'], true)) {
            return $next($request);
        }

//        array_search($request->getPathInfo(), app()->router->namedRoutes)
//        app()->router->namedRoutes

        $paramCache = $request->input('cache', 'false');
        $pathInfo = ltrim($request->getPathInfo(), '/');
        if (in_array($pathInfo, [
                'v1.0/provinces',
                'v1.0/references',
                'v1.0/media-categories',
                'v1.0/influencer/curation',
            ]) || Str::endsWith($pathInfo, 'recent-posts')
            || Str::endsWith($pathInfo, 'campaign-information')
            || $paramCache === 'true'
        ) {
            //|| Str::startsWith($pathInfo, 'v1.0/influencer/')
            $useCached = true;

            if (Str::endsWith($pathInfo, 'campaign-information')) {
                $this->cacheExpired = 5;
            }
        }
        $useCached = CacheRedis::checkCacheConnection() && ($useCached ?? false);
        if ($useCached) {
            $args = array_merge([
                'url' => $request->url(),
                'method' => $request->method(),
                'token' => $request->bearerToken()
            ], $request->except('cache'));
            ksort($args);

            $pathInfo = str_replace('/', '_', $pathInfo);
            if (Str::endsWith($pathInfo, 'recent-posts')) {
                $pathInfo = 'v1.0/recent-posts-' . $request->input('media', 'instagram');
            }

            // EX: middleware:v1.0_featured-search:{hash}
            $lockName = 'middleware:' . $pathInfo . ':' . md5(json_encode($args));

            // Cache::get($lockName)
            $cachedData = CacheRedis::get($lockName);
            if (!empty($cachedData)) {
                return response()->json($cachedData);
            }
        }

        $response = $next($request);
        if ($useCached) {
            try {
                $data = $response->getData();
                if ((is_array($data) && empty($data["error"] && !empty($data["data"])))
                    || (is_object($data) && empty($data->error) && !empty($data->data))
                ) {
//                Cache::add($lockName, $data, $this->waitToSecond);
                    CacheRedis::set($lockName, $data, $this->cacheExpired);
                }
            } catch (Exception $ex) {
            }
        }

        return $response;
    }
}
