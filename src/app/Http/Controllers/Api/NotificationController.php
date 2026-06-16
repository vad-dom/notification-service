<?php

namespace App\Http\Controllers\Api;

use App\DTO\CreateNotificationBatchData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotificationBatchRequest;
use App\Http\Resources\NotificationBatchResource;
use App\Services\NotificationBatchService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller
{
    #[OA\Post(
        path: '/notification-batches',
        summary: 'Create notification batch',
        security: [['ApiToken' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
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
                ]
            )
        ),
        tags: ['Notification batches'],
        parameters: [
            new OA\Parameter(
                name: 'Idempotency-Key',
                description: 'Unique UUID key for idempotent request processing',
                in: 'header',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
                example: '550e8400-e29b-41d4-a716-446655440000'
            ),
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Batch created',
                content: new OA\JsonContent(ref: '#/components/schemas/NotificationBatchResponse')
            ),
            new OA\Response(
                response: 200,
                description: 'Batch already exists for this Idempotency-Key',
                content: new OA\JsonContent(ref: '#/components/schemas/NotificationBatchResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid API token'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
            new OA\Response(
                response: 429,
                description: 'Too many notification batch requests'
            ),
        ]
    )]
    public function store(
        StoreNotificationBatchRequest $request,
        NotificationBatchService $service
    ): JsonResponse {
        $result = $service->create(
            CreateNotificationBatchData::fromArray($request->validated()),
            $request->header('Idempotency-Key')
        );

        $statusCode = $result->created
            ? Response::HTTP_CREATED
            : Response::HTTP_OK;

        return (new NotificationBatchResource($result->batch))
            ->response()
            ->setStatusCode($statusCode);
    }
}
