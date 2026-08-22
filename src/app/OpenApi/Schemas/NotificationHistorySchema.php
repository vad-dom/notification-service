<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'NotificationHistoryResponse',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/NotificationHistoryItem')
        ),
        new OA\Property(
            property: 'links',
            properties: [
                new OA\Property(property: 'first', type: 'string', nullable: true),
                new OA\Property(property: 'last', type: 'string', nullable: true),
                new OA\Property(property: 'prev', type: 'string', nullable: true),
                new OA\Property(property: 'next', type: 'string', nullable: true),
            ],
            type: 'object'
        ),
        new OA\Property(
            property: 'meta',
            properties: [
                new OA\Property(property: 'path', type: 'string', example: 'http://localhost:8080/api/recipients/1/notifications'),
                new OA\Property(property: 'per_page', type: 'integer', example: 15),
                new OA\Property(property: 'next_cursor', type: 'string', nullable: true),
                new OA\Property(property: 'prev_cursor', type: 'string', nullable: true),
            ],
            type: 'object'
        ),
    ],
    type: 'object'
)]
class NotificationHistorySchema {}
