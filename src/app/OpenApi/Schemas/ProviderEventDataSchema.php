<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProviderEventData',
    properties: [
        new OA\Property(
            property: 'id',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'status',
            type: 'string',
            example: 'delivered'
        ),
    ],
    type: 'object'
)]
class ProviderEventDataSchema {}
