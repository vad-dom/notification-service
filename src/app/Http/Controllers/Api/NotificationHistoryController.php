<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationHistoryResource;
use App\Models\Recipient;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationHistoryController extends Controller
{
    public function index(Recipient $recipient): AnonymousResourceCollection
    {
        $notifications = $recipient->notifications()
            ->with('batch')
            ->latest()
            ->get();

        return NotificationHistoryResource::collection($notifications);
    }
}
