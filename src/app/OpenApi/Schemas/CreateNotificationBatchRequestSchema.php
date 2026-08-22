<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CreateNotificationBatchRequest',
    required: ['channel', 'type', 'message', 'recipient_ids'],
    properties: [
        new OA\Property(
            property: 'channel',
            type: 'string',
            example: 'sms',
            enum: ['sms', 'email']
        ),
        new OA\Property(
            property: 'type',
            type: 'string',
            example: 'transactional',
            enum: ['transactional', 'marketing']
        ),
        new OA\Property(
            property: 'message',
            type: 'string',
            example: 'Your code: 1234'
        ),
        new OA\Property(
            property: 'recipient_ids',
            type: 'array',
            items: new OA\Items(type: 'integer'),
            example: [1, 2]
        ),
    ],
    type: 'object'
)]
class CreateNotificationBatchRequestSchema {}
