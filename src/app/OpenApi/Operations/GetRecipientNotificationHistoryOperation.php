<?php

namespace App\OpenApi\Operations;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/recipients/{recipient}/notifications',
    summary: 'Get recipient notification history',
    security: [['ApiToken' => []]],
    tags: ['Notification history'],
    parameters: [
        new OA\Parameter(
            name: 'recipient',
            description: 'Recipient ID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'integer'),
            example: 1
        ),
        new OA\Parameter(
            name: 'cursor',
            description: 'Cursor for the next page of results',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string'),
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Recipient notification history',
            content: new OA\JsonContent(ref: '#/components/schemas/NotificationHistoryResponse')
        ),
        new OA\Response(
            response: 401,
            description: 'Invalid API token'
        ),
        new OA\Response(
            response: 404,
            description: 'Recipient not found'
        ),
    ]
)]
class GetRecipientNotificationHistoryOperation {}
