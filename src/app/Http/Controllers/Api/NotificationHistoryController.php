<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationHistoryResource;
use App\Models\Recipient;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class NotificationHistoryController extends Controller
{
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
    public function index(Recipient $recipient): AnonymousResourceCollection
    {
        $notifications = $recipient->notifications()
            ->with('batch')
            ->latest()
            ->get();

        return NotificationHistoryResource::collection($notifications);
    }
}
