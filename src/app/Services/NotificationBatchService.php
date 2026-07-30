<?php

namespace App\Services;

use App\DTO\CreateNotificationBatchData;
use App\DTO\NotificationBatchCreationResult;
use App\Enums\NotificationStatus;
use App\Exceptions\IdempotencyLockTimeoutException;
use App\Models\NotificationBatch;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

readonly class NotificationBatchService
{
    private const int LOCK_TTL_SECONDS = 30;

    private const int LOCK_WAIT_SECONDS = 10;

    public function __construct(
        private NotificationPublisher $publisher
    ) {}

    public function create(CreateNotificationBatchData $data, string $idempotencyKey): NotificationBatchCreationResult
    {
        $existingBatch = $this->existingBatchResult($idempotencyKey);

        if ($existingBatch) {
            return $existingBatch;
        }

        $lockKey = 'notification-batch:idempotency:'.hash('sha256', $idempotencyKey);

        $lock = Cache::lock($lockKey, self::LOCK_TTL_SECONDS);

        try {
            return $lock->block(self::LOCK_WAIT_SECONDS, function () use ($data, $idempotencyKey) {
                $existingBatch = $this->existingBatchResult($idempotencyKey);

                if ($existingBatch) {
                    return $existingBatch;
                }

                return DB::transaction(function () use ($data, $idempotencyKey) {
                    $type = $data->type;

                    $batch = NotificationBatch::query()->create([
                        'channel' => $data->channel,
                        'type' => $type,
                        'message' => $data->message,
                        'idempotency_key' => $idempotencyKey,
                    ]);

                    $priority = $type->priority();

                    foreach ($data->recipientIds as $recipientId) {
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

                    return new NotificationBatchCreationResult(
                        batch: $batch->loadCount('notifications'),
                        created: true,
                    );
                });
            });
        } catch (LockTimeoutException $exception) {
            throw new IdempotencyLockTimeoutException(previous: $exception);
        }
    }

    private function findExistingBatch(string $idempotencyKey): ?NotificationBatch
    {
        return NotificationBatch::query()
            ->where('idempotency_key', $idempotencyKey)
            ->withCount('notifications')
            ->first();
    }

    private function existingBatchResult(string $idempotencyKey): ?NotificationBatchCreationResult
    {
        $batch = $this->findExistingBatch($idempotencyKey);

        if (! $batch) {
            return null;
        }

        return new NotificationBatchCreationResult(
            batch: $batch,
            created: false,
        );
    }
}
