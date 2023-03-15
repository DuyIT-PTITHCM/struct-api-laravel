<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class BasicAuthMiddleware
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
        $token = $request->header('auth-token');
        if (!empty($token)) {
            try {
                $user = env('key_user');
                $password = env('key_partner');
                if (Crypt::decrypt($token) === "{$user}:{$password}") {
                    return $next($request);
                }
            } catch (Exception $ex) {
            }
        }

        return response()->json([
            'error' => 'Unauthorized BasicAuth',
        ], 401);
    }
}
