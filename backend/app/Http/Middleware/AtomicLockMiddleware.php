<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AtomicLockMiddleware
{
    private int $waitToSecond = 3; // 3 seconds to run this request again

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS', 'TRACE'], true)) {
            return $next($request);
        }

        $args = array_merge([
            'url' => $request->url(),
            'method' => $request->method(),
            'token' => $request->bearerToken()
        ], $request->all());
        ksort($args);

        $lockName = 'AtomicLockMiddleware:' . md5(json_encode($args));

        if (Cache::add($lockName, 'true', $this->waitToSecond)) {
            return $next($request);
        } else {
            return response()->json([
                'code' => 'wait_a_moment',
                'message' => "Wait a moment"
            ], 403);
        }
    }
}
