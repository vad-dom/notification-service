<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotificationBatchRequest;
use App\Http\Resources\NotificationBatchResource;
use App\Services\NotificationBatchService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller
{
    public function store(
        StoreNotificationBatchRequest $request,
        NotificationBatchService $service
    ): JsonResponse {
        $result = $service->create(
            $request->validated(),
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
