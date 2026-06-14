<?php

namespace App\Jobs;

use App\Enums\NotificationStatus;
use App\Models\Notification;
use App\Services\NotificationProviderResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SendNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $notificationId)
    {
        //
    }

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
}
