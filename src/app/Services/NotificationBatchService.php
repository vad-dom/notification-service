<?php

namespace App\Services;

use App\Enums\NotificationPriority;
use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use App\Models\NotificationBatch;
use Illuminate\Support\Facades\DB;

class NotificationBatchService
{
    public function create(array $data, string $idempotencyKey): NotificationBatch
    {
        return DB::transaction(function () use ($data, $idempotencyKey) {
            $existingBatch = NotificationBatch::query()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existingBatch) {
                return $existingBatch->load('notifications');
            }

            $batch = NotificationBatch::query()->create([
                'channel' => $data['channel'],
                'type' => $data['type'],
                'message' => $data['message'],
                'idempotency_key' => $idempotencyKey,
            ]);

            $priority = $data['type'] === NotificationType::Transactional->value
                ? NotificationPriority::Transactional
                : NotificationPriority::Marketing;

            foreach ($data['recipient_ids'] as $recipientId) {
                $batch->notifications()->create([
                    'recipient_id' => $recipientId,
                    'status' => NotificationStatus::Queued,
                    'priority' => $priority,
                    'queued_at' => now(),
                ]);
            }

            return $batch->load('notifications');
        });
    }
}
