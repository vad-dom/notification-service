<?php

namespace App\Http\RateLimiting;

use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

readonly class NotificationBatchRateLimiter
{
    public function __construct(
        private RateLimiter $limiter
    ) {}

    public function enforce(Request $request): void
    {
        $limit = (int) config('notifications.rate_limit.batch_requests_per_minute');
        $decaySeconds = (int) config('notifications.rate_limit.batch_decay_seconds');

        $token = $request->bearerToken() ?? '';

        $key = 'notification-batches:create:api-token:'.sha1($token);

        if ($this->limiter->tooManyAttempts($key, $limit)) {
            throw new HttpResponseException(
                response()
                    ->json([
                        'message' => 'Too many notification batch requests.',
                    ], Response::HTTP_TOO_MANY_REQUESTS)
                    ->withHeaders([
                        'Retry-After' => $this->limiter->availableIn($key),
                    ])
            );
        }

        $this->limiter->hit($key, $decaySeconds);
    }
}
