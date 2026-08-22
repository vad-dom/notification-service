<?php

namespace App\Http\Controllers\Api;

use App\DTO\ProviderDeliveryStatusData;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProviderDeliveryStatusRequest;
use App\Http\Resources\ProviderEventResource;
use App\Services\ProviderEventService;

class ProviderEventController extends Controller
{
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
