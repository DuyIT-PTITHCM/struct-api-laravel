<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $headers = [
            // 'Access-Control-Allow-Origin' => getenv('ACCESS_CONTROL_ALLOW_ORIGIN'),
            // 'Access-Control-Allow-Methods' => getenv('ACCESS_CONTROL_ALLOW_METHODS'),
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS, PUT, DELETE, PATCH, PUT',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age' => '86400',
            'Access-Control-Allow-Headers' => 'Content-Type, Cache-Control, Authorization, X-Requested-With, auth-token'
        ];

        if ($request->isMethod('OPTIONS')) {
            return response()->json('{"method":"OPTIONS"}', 200, $headers);
        }

        $response = $next($request);
        if (!in_array(get_class($response), ['Symfony\Component\HttpFoundation\BinaryFileResponse'])) {
            foreach ($headers as $key => $value) {
                $response->header($key, $value);
            }
        } else {
            foreach ($headers as $key => $value) {
                header($key . ': ' . $value);
            }
        }

        return $response;
    }
}
