<?php

namespace App\Services;

use App\DTO\CreateNotificationBatchData;
use App\DTO\NotificationBatchCreationResult;
use App\Enums\NotificationStatus;
use App\Exceptions\IdempotencyLockTimeoutException;
use App\Jobs\PublishNotificationOutboxJob;
use App\Models\NotificationBatch;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

readonly class NotificationBatchService
{
    private const int LOCK_TTL_SECONDS = 30;

    private const int LOCK_WAIT_SECONDS = 10;

    public function __construct(
        private NotificationOutboxService $outboxService
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

                $result = DB::transaction(function () use ($data, $idempotencyKey) {
                    $type = $data->type;

                    $batch = NotificationBatch::query()->create([
                        'channel' => $data->channel,
                        'type' => $type,
                        'message' => $data->message,
                        'idempotency_key' => $idempotencyKey,
                    ]);

                    $priority = $type->priority();
                    $queueName = $type->queueName();

                    foreach ($data->recipientIds as $recipientId) {
                        $notification = $batch->notifications()->create([
                            'recipient_id' => $recipientId,
                            'status' => NotificationStatus::Pending,
                            'priority' => $priority,
                        ]);

                        $this->outboxService->record($notification, $queueName);
                    }

                    return new NotificationBatchCreationResult(
                        batch: $batch->loadCount('notifications'),
                        created: true,
                    );
                });

                $this->scheduleOutboxRelay($result->batch->id);

                return $result;
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

        $this->scheduleOutboxRelay($batch->id);

        return new NotificationBatchCreationResult(
            batch: $batch,
            created: false,
        );
    }

    private function scheduleOutboxRelay(int $batchId): void
    {
        PublishNotificationOutboxJob::dispatch($batchId)->afterCommit();
    }
}
