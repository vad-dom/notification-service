<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
        if ($request->header('X-Provider-Token') !== config('services.provider_event.token')) {
            return response()->json([
                'message' => 'Invalid provider token.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
