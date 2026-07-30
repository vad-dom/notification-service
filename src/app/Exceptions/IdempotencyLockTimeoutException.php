<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class IdempotencyLockTimeoutException extends RuntimeException
{
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(
            message: 'Request with this Idempotency-Key is still being processed.',
            previous: $previous,
        );
    }

    public function render(Request $request): JsonResponse
    {
        return response()
            ->json([
                'message' => $this->getMessage(),
            ], Response::HTTP_SERVICE_UNAVAILABLE)
            ->withHeaders([
                'Retry-After' => '5',
            ]);
    }
}
