<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class EnsureProviderToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (empty(config('services.provider_event.token'))) {
            throw new RuntimeException('PROVIDER_EVENT_TOKEN is not configured.');
        }

        $token = $request->header('X-Provider-Token');

        if (! hash_equals((string) config('services.provider_event.token'), (string) $token)) {
            return response()->json([
                'message' => 'Invalid provider token.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
