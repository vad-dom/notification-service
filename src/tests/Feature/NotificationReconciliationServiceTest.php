<?php

namespace Tests\Feature;

use App\Enums\NotificationStatus;
use App\Jobs\SendNotificationJob;
use App\Models\Notification;
use App\Services\NotificationReconciliationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotificationReconciliationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_republish_stuck_queued_notifications(): void
    {
        Queue::fake();

        config([
            'notifications.reconciliation.stuck_queued_threshold_minutes' => 5,
        ]);

        $stuckNotification = Notification::factory()->create([
            'status' => NotificationStatus::Queued,
            'queued_at' => now()->subMinutes(10),
        ]);

        Notification::factory()->create([
            'status' => NotificationStatus::Queued,
            'queued_at' => now()->subMinute(),
        ]);

        $republishedCount = app(NotificationReconciliationService::class)->republishStuckQueued(100);

        $this->assertSame(1, $republishedCount);

        $stuckNotification->refresh();
        $this->assertSame(NotificationStatus::ReconciliationPending, $stuckNotification->status);

        Queue::assertPushed(SendNotificationJob::class, function (SendNotificationJob $job) use ($stuckNotification) {
            return $job->notificationId === $stuckNotification->id;
        });
    }

    public function test_republish_stuck_queued_ignores_non_queued_notifications(): void
    {
        Queue::fake();

        Notification::factory()->create([
            'status' => NotificationStatus::Sent,
            'queued_at' => now()->subMinutes(10),
            'sent_at' => now()->subMinutes(9),
        ]);

        $republishedCount = app(NotificationReconciliationService::class)->republishStuckQueued(100);

        $this->assertSame(0, $republishedCount);
        Queue::assertNothingPushed();
    }

    public function test_republish_does_not_run_twice_for_reconciliation_pending_notifications(): void
    {
        Queue::fake();

        config([
            'notifications.reconciliation.stuck_queued_threshold_minutes' => 5,
        ]);

        Notification::factory()->create([
            'status' => NotificationStatus::ReconciliationPending,
            'queued_at' => now()->subMinutes(10),
        ]);

        $republishedCount = app(NotificationReconciliationService::class)->republishStuckQueued(100);

        $this->assertSame(0, $republishedCount);
        Queue::assertNothingPushed();
    }
}
