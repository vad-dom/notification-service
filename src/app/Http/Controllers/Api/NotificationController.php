<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotificationBatchRequest;
use App\Services\NotificationBatchService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller
{
    public function store(
        StoreNotificationBatchRequest $request,
        NotificationBatchService $service
    ): JsonResponse {
        $batch = $service->create(
            $request->validated(),
            $request->header('Idempotency-Key')
        );

        return response()->json([
            'data' => $batch,
        ], Response::HTTP_CREATED);
    }
}
