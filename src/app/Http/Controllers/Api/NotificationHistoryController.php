<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationHistoryResource;
use App\Models\Recipient;
use App\Services\NotificationHistoryService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationHistoryController extends Controller
{
    public function index(
        Recipient $recipient,
        NotificationHistoryService $service
    ): AnonymousResourceCollection {
        return NotificationHistoryResource::collection(
            $service->paginateForRecipient($recipient)
        );
    }
}
