<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'NotificationBatchData',
    properties: [
        new OA\Property(
            property: 'id',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'status',
            type: 'string',
            example: 'accepted'
        ),
        new OA\Property(
            property: 'notifications_count',
            type: 'integer',
            example: 2
        ),
    ],
    type: 'object'
)]
class NotificationBatchDataSchema {}
