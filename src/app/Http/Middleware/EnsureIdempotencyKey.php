<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureIdempotencyKey
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $idempotencyKey = $request->header('Idempotency-Key');

        if (! $idempotencyKey) {
            return response()->json([
                'message' => 'Idempotency-Key header is required.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! Str::isUuid($idempotencyKey)) {
            return response()->json([
                'message' => 'Idempotency-Key header must be a valid UUID.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $next($request);
    }
}
