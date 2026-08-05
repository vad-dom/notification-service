<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class UnexpectedNotificationStatusException extends HttpDomainException
{
    public function statusCode(): int
    {
        return Response::HTTP_CONFLICT;
    }
}
