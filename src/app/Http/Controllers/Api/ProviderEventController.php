<?php

namespace App\Http\Controllers\Api;

use App\DTO\ProviderDeliveryStatusData;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProviderDeliveryStatusRequest;
use App\Http\Resources\ProviderEventResource;
use App\Services\ProviderEventService;
use OpenApi\Attributes as OA;

class ProviderEventController extends Controller
{
    #[OA\Post(
        path: '/provider-events/delivery-status',
        summary: 'Handle provider delivery status event',
        security: [['ProviderToken' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
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
                ]
            )
        ),
        tags: ['Provider events'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Provider event accepted',
                content: new OA\JsonContent(ref: '#/components/schemas/ProviderEventResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid provider token'
            ),
            new OA\Response(
                response: 404,
                description: 'Notification not found'
            ),
            new OA\Response(
                response: 409,
                description: 'Notification status cannot be changed'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
        ]
    )]
    public function deliveryStatus(
        ProviderDeliveryStatusRequest $request,
        ProviderEventService $service
    ): ProviderEventResource {
        $notification = $service->updateDeliveryStatus(
            ProviderDeliveryStatusData::fromArray($request->validated())
        );

        return new ProviderEventResource($notification);
    }
}
