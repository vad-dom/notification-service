<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProviderEventResponse',
    properties: [
        new OA\Property(
            property: 'data',
            ref: '#/components/schemas/ProviderEventData'
        ),
    ],
    type: 'object'
)]
class ProviderEventSchema {}
