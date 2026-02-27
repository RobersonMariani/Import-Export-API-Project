<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FeatureFlagMiddleware
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if (! config("features.{$feature}.enabled", false)) {
            return response()->json([
                'message' => 'This feature is currently disabled.',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return $next($request);
    }
}
