<?php

namespace App\Services;

use App\Enums\NotificationStatus;
use App\Models\Notification;
use Illuminate\Support\Carbon;

class NotificationReconciliationService
{
    public function __construct(
        private NotificationPublisher $publisher
    ) {}

    public function republishStuckQueued(int $limit): int
    {
        $thresholdMinutes = (int) config('notifications.reconciliation.stuck_queued_threshold_minutes');
        $staleBefore = Carbon::now()->subMinutes($thresholdMinutes);

        $notifications = Notification::query()
            ->where('status', NotificationStatus::Queued)
            ->where('queued_at', '<=', $staleBefore)
            ->orderBy('queued_at')
            ->limit($limit)
            ->get();

        foreach ($notifications as $notification) {
            $this->publisher->publish($notification);

            $notification->update([
                'status' => NotificationStatus::ReconciliationPending,
            ]);
        }

        return $notifications->count();
    }
}
