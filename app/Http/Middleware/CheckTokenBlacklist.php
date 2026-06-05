<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks tokens that have been manually blacklisted
 * (e.g., after password change or account suspension)
 */
class CheckTokenBlacklist
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->user()?->currentAccessToken();

        if ($token && Cache::has("blacklisted_token:{$token->id}")) {
            $token->delete();
            return response()->json([
                'success' => false,
                'message' => 'Token has been revoked. Please login again.',
            ], 401);
        }

        return $next($request);
    }
}
