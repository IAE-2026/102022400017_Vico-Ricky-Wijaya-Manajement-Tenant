<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApiKey
{
    /**
     * Handle an incoming request.
     * Validates X-IAE-KEY header per Standard Integration Contract (IAE-T2)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-IAE-KEY');

        if (empty($apiKey)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Missing API Key. Please provide X-IAE-KEY in the request header.',
                'errors'  => null,
            ], 401);
        }

        if ($apiKey !== config('app.iae_api_key')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid API Key.',
                'errors'  => null,
            ], 403);
        }

        return $next($request);
    }
}
