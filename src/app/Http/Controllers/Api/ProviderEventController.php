<?php

namespace App\Http\Controllers\Api;

use App\DTO\ProviderDeliveryStatusData;
use App\Exceptions\UnexpectedNotificationStatusException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProviderDeliveryStatusRequest;
use App\Services\ProviderEventService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ProviderEventController extends Controller
{
    public function deliveryStatus(
        ProviderDeliveryStatusRequest $request,
        ProviderEventService $service
    ): JsonResponse {
        try {
            $notification = $service->updateDeliveryStatus(
                ProviderDeliveryStatusData::fromArray($request->validated())
            );
        } catch (UnexpectedNotificationStatusException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], Response::HTTP_CONFLICT);
        }

        return response()->json([
            'data' => [
                'id' => $notification->id,
                'status' => $notification->status,
            ],
        ]);
    }
}
