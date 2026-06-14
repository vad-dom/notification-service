<?php

namespace App\Services;

use App\Jobs\SendNotificationJob;
use App\Models\Notification;

class NotificationPublisher
{
    public function publish(Notification $notification): void
    {
        $notification->loadMissing('batch');

        SendNotificationJob::dispatch($notification->id)
            ->onQueue($notification->batch->type->queueName());
    }
}
