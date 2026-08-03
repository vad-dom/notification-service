<?php

namespace App\Jobs;

use App\Services\NotificationOutboxService;
use App\Support\ManualTestDelay;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PublishNotificationOutboxJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $batchId)
    {
        $this->onQueue(config('notifications.outbox.relay_queue'));
    }

    public function handle(NotificationOutboxService $outboxService): void
    {
        ManualTestDelay::apply('outbox_relay');

        $outboxService->publishPendingForBatch($this->batchId);
    }
}
