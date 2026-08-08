<?php

namespace App\Jobs;

use App\Enums\NotificationPriority;
use App\Enums\NotificationStatus;
use App\Models\Notification;
use App\Services\NotificationProviderResolver;
use App\Support\ManualTestDelay;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $notificationId,
        public int $priority = NotificationPriority::Normal->value,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(NotificationProviderResolver $resolver): void
    {
        $lock = Cache::lock("notification:{$this->notificationId}", 30);

        if (! $lock->get()) {
            return;
        }

        try {
            $notification = Notification::query()
                ->with(['batch', 'recipient'])
                ->findOrFail($this->notificationId);

            if ($notification->status !== NotificationStatus::Queued) {
                return;
            }

            ManualTestDelay::apply('send_notification');

            $provider = $resolver->resolve($notification);

            $providerMessageId = $provider->send($notification);

            $notification->update([
                'status' => NotificationStatus::Sent,
                'provider_message_id' => $providerMessageId,
                'sent_at' => now(),
            ]);

            Log::info('Notification sent to provider', [
                'notification_id' => $notification->id,
                'provider_message_id' => $providerMessageId,
            ]);
        } finally {
            $lock->release();
        }
    }

    public function failed(Throwable $exception): void
    {
        $notification = Notification::find($this->notificationId);

        if (! $notification) {
            Log::error('Notification job failed, notification was not found', [
                'notification_id' => $this->notificationId,
                'error' => $exception->getMessage(),
            ]);

            return;
        }

        $notification->update([
            'status' => NotificationStatus::Discarded,
            'failure_reason' => $exception->getMessage(),
        ]);

        Log::error('Notification job failed', [
            'notification_id' => $notification->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
