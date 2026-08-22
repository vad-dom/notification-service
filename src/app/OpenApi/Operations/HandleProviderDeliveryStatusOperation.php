<?php

namespace App\OpenApi\Operations;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/provider-events/delivery-status',
    summary: 'Handle provider delivery status event',
    security: [['ProviderToken' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: '#/components/schemas/ProviderDeliveryStatusRequest')
    ),
    tags: ['Provider events'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Provider event accepted',
            content: new OA\JsonContent(ref: '#/components/schemas/ProviderEventResponse')
        ),
        new OA\Response(
            response: 401,
            description: 'Invalid provider token'
        ),
        new OA\Response(
            response: 404,
            description: 'Notification not found'
        ),
        new OA\Response(
            response: 409,
            description: 'Notification status cannot be changed'
        ),
        new OA\Response(
            response: 422,
            description: 'Validation error'
        ),
    ]
)]
class HandleProviderDeliveryStatusOperation {}
