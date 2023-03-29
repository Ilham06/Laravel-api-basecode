<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        if(!$token) {
            // Unauthorized response if token not there
            return response()->json([
                'error' => 'Token not provided.',
                'status' => 'TOKEN_NOT_PROVIDED'
            ], 400);
        }
        try {
            $credentials = JWT::decode($token, new Key(config('jwt.secret'), config('jwt.algo')));
        } catch(ExpiredException $e) {
            return response()->json([
                'error' => 'Provided token is expired.',
                'status' => "TOKEN_EXPIRED"
            ], 401);
        } catch(Exception $e) {
            return response()->json([
                'error' => 'An error while decoding token.',
                'status' => 'TOKEN_FAILED'
            ], 401);
        }
        return $next($request);
    }
}
