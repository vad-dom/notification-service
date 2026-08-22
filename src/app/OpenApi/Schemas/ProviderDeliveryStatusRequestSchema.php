<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProviderDeliveryStatusRequest',
    required: ['provider_message_id', 'status'],
    properties: [
        new OA\Property(
            property: 'provider_message_id',
            type: 'string',
            example: 'sms-1'
        ),
        new OA\Property(
            property: 'status',
            type: 'string',
            example: 'delivered',
            enum: ['delivered', 'discarded']
        ),
        new OA\Property(
            property: 'failure_reason',
            type: 'string',
            example: 'Invalid phone number',
            nullable: true
        ),
    ],
    type: 'object'
)]
class ProviderDeliveryStatusRequestSchema {}
