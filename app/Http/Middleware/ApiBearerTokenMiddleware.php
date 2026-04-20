<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ApiBearerTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = (string) config('services.webapp.api_bearer_token');
        $providedToken = (string) $request->bearerToken();

        if ($providedToken === '') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. Invalid bearer token.',
            ], 401);
        }

        if ($expectedToken !== '' && hash_equals($expectedToken, $providedToken)) {
            return $next($request);
        }

        $temporaryTokenHash = hash('sha256', $providedToken);
        if (Cache::has("webapp:api-token:{$temporaryTokenHash}")) {
            return $next($request);
        }

        return response()->json([
            'status' => false,
            'message' => 'Unauthorized. Invalid bearer token.',
        ], 401);
    }
}
