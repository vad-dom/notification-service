<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if ($token !== config('services.api.token')) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
