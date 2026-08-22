<?php

namespace App\OpenApi\Parameters;

use OpenApi\Attributes as OA;

#[OA\Parameter(
    parameter: 'IdempotencyKey',
    name: 'Idempotency-Key',
    description: 'Unique UUID key for idempotent request processing',
    in: 'header',
    required: true,
    schema: new OA\Schema(type: 'string', format: 'uuid'),
    example: '550e8400-e29b-41d4-a716-446655440000'
)]
class IdempotencyKeyParameter {}
