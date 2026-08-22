<?php

namespace App\OpenApi\Operations;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/notification-batches',
    summary: 'Create notification batch',
    security: [['ApiToken' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: '#/components/schemas/CreateNotificationBatchRequest')
    ),
    tags: ['Notification batches'],
    parameters: [
        new OA\Parameter(ref: '#/components/parameters/IdempotencyKey'),
    ],
    responses: [
        new OA\Response(
            response: 201,
            description: 'Batch created',
            content: new OA\JsonContent(ref: '#/components/schemas/NotificationBatchResponse')
        ),
        new OA\Response(
            response: 200,
            description: 'Batch already exists for this Idempotency-Key',
            content: new OA\JsonContent(ref: '#/components/schemas/NotificationBatchResponse')
        ),
        new OA\Response(
            response: 401,
            description: 'Invalid API token'
        ),
        new OA\Response(
            response: 422,
            description: 'Validation error'
        ),
        new OA\Response(
            response: 429,
            description: 'Too many notification batch requests'
        ),
    ]
)]
class CreateNotificationBatchOperation {}
