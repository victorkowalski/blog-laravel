<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use App\Models\User;

class JwtMiddleware
{
    public function handle($request, Closure $next, $guard = null)
    {
        $token = $request->bearerToken();

        if(!$token) {
            // Unauthorized response if token not there
            return response()->json(['status' => 'error', 'message' => 'Token not provided']);
        }

        $user = User::whereHas('accessToken', function($q) use ($token) {
            $q->where('token', $token);
         })->get();

        if(!$user) {
            // Unauthorized response if token not there
            return response()->json([
                'error' => 'Not authorized.'
            ], 401);
        }
        // Now let's put the user in the request class so that you can grab it from there
        $request->auth = $user;

        return $next($request);
    }
}