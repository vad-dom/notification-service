<?php

namespace App\Services;

use App\Enums\NotificationStatus;
use App\Models\Notification;
use App\Models\NotificationOutbox;
use Illuminate\Database\Eloquent\Builder;

class NotificationOutboxService
{
    public function __construct(
        private NotificationPublisher $publisher
    ) {}

    public function record(Notification $notification, string $queueName): NotificationOutbox
    {
        return NotificationOutbox::query()->create([
            'notification_id' => $notification->id,
            'queue_name' => $queueName,
            'priority' => $notification->priority,
        ]);
    }

    public function publishPendingForBatch(int $batchId): void
    {
        $this->pendingOutboxQuery()
            ->whereHas('notification', fn ($query) => $query->where('notification_batch_id', $batchId))
            ->get()
            ->each(fn (NotificationOutbox $entry) => $this->publishEntry($entry));
    }

    public function publishPending(int $limit = 100): int
    {
        $entries = $this->pendingOutboxQuery()
            ->limit($limit)
            ->get();

        foreach ($entries as $entry) {
            $this->publishEntry($entry);
        }

        return $entries->count();
    }

    public function publishEntry(NotificationOutbox $entry): void
    {
        if ($entry->isPublished()) {
            return;
        }

        $notification = $entry->notification;

        if ($notification->status !== NotificationStatus::Pending) {
            $entry->update(['published_at' => now()]);

            return;
        }

        $this->publisher->publish($notification);

        $notification->update([
            'status' => NotificationStatus::Queued,
            'queued_at' => now(),
        ]);

        $entry->update(['published_at' => now()]);
    }

    private function pendingOutboxQuery(): Builder
    {
        return NotificationOutbox::query()
            ->whereNull('published_at')
            ->with('notification')
            ->orderByDesc('priority')
            ->orderBy('id');
    }
}
