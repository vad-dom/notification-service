<?php

namespace App\Services;

use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use App\Models\NotificationBatch;
use Illuminate\Support\Facades\DB;

readonly class NotificationBatchService
{
    public function __construct(
        private NotificationPublisher $publisher
    ) {}

    public function create(array $data, string $idempotencyKey): NotificationBatch
    {
        return DB::transaction(function () use ($data, $idempotencyKey) {
            $existingBatch = NotificationBatch::query()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existingBatch) {
                return $existingBatch->loadCount('notifications');
            }

            $type = NotificationType::from($data['type']);

            $batch = NotificationBatch::query()->create([
                'channel' => $data['channel'],
                'type' => $type,
                'message' => $data['message'],
                'idempotency_key' => $idempotencyKey,
            ]);

            $priority = $type->priority();

            foreach ($data['recipient_ids'] as $recipientId) {
                $notification = $batch->notifications()->create([
                    'recipient_id' => $recipientId,
                    'status' => NotificationStatus::Pending,
                    'priority' => $priority,
                ]);

                $this->publisher->publish($notification);

                $notification->update([
                    'status' => NotificationStatus::Queued,
                    'queued_at' => now(),
                ]);
            }

            return $batch->loadCount('notifications');
        });
    }
}
