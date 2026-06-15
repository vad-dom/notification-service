<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'NotificationHistoryResponse',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'channel', type: 'string', example: 'sms'),
                    new OA\Property(property: 'type', type: 'string', example: 'transactional'),
                    new OA\Property(property: 'message', type: 'string', example: 'Your code: 1234'),
                    new OA\Property(property: 'status', type: 'string', example: 'sent'),
                    new OA\Property(property: 'provider_message_id', type: 'string', example: 'sms-1', nullable: true),
                    new OA\Property(property: 'failure_reason', type: 'string', example: null, nullable: true),
                    new OA\Property(property: 'queued_at', type: 'string', format: 'date-time', nullable: true),
                    new OA\Property(property: 'sent_at', type: 'string', format: 'date-time', nullable: true),
                    new OA\Property(property: 'delivered_at', type: 'string', format: 'date-time', nullable: true),
                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                ],
                type: 'object'
            )
        ),
    ],
    type: 'object'
)]
class NotificationHistorySchema {}
