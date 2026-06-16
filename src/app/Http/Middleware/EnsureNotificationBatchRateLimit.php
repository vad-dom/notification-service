<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

readonly class EnsureNotificationBatchRateLimit
{
    public function __construct(
        private RateLimiter $limiter
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $limit = (int) config('notifications.rate_limit.batch_requests_per_minute');

        $token = $request->bearerToken();

        $key = 'notification-batches:create:api-token:'.sha1($token);

        if ($this->limiter->tooManyAttempts($key, $limit)) {
            return response()->json([
                'message' => 'Too many notification batch requests.',
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        $this->limiter->hit($key);

        return $next($request);
    }
}
