<?php

namespace Tests\Feature;

use App\Enums\NotificationPriority;
use App\Enums\NotificationStatus;
use App\Jobs\SendNotificationJob;
use App\Models\Notification;
use App\Models\NotificationOutbox;
use App\Services\NotificationOutboxService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotificationOutboxServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_publish_entry_dispatches_job_and_marks_notification_as_queued(): void
    {
        Queue::fake();

        $notification = Notification::factory()->create([
            'status' => NotificationStatus::Pending,
            'queued_at' => null,
        ]);

        $entry = NotificationOutbox::factory()->create([
            'notification_id' => $notification->id,
            'queue_name' => 'notifications.critical',
            'published_at' => null,
        ]);

        app(NotificationOutboxService::class)->publishEntry($entry);

        $notification->refresh();
        $entry->refresh();

        $this->assertSame(NotificationStatus::Queued, $notification->status);
        $this->assertNotNull($notification->queued_at);
        $this->assertNotNull($entry->published_at);

        Queue::assertPushed(SendNotificationJob::class, function (SendNotificationJob $job) use ($notification): bool {
            return $job->notificationId === $notification->id;
        });
    }

    public function test_publish_entry_is_idempotent(): void
    {
        Queue::fake();

        $notification = Notification::factory()->create([
            'status' => NotificationStatus::Queued,
            'queued_at' => now(),
        ]);

        $entry = NotificationOutbox::factory()->create([
            'notification_id' => $notification->id,
            'queue_name' => 'notifications.critical',
            'published_at' => null,
        ]);

        app(NotificationOutboxService::class)->publishEntry($entry);

        $entry->refresh();

        $this->assertNotNull($entry->published_at);
        Queue::assertNothingPushed();
    }

    public function test_publish_pending_processes_unpublished_entries(): void
    {
        Queue::fake();

        foreach (range(1, 2) as $index) {
            NotificationOutbox::factory()->create([
                'notification_id' => Notification::factory()->create([
                    'status' => NotificationStatus::Pending,
                    'queued_at' => null,
                ]),
                'published_at' => null,
            ]);
        }

        NotificationOutbox::factory()
            ->published()
            ->create();

        $publishedCount = app(NotificationOutboxService::class)->publishPending();

        $this->assertSame(2, $publishedCount);
        Queue::assertPushed(SendNotificationJob::class, 2);
    }

    public function test_publish_pending_processes_urgent_entries_first(): void
    {
        Queue::fake();

        $normalNotification = Notification::factory()->create([
            'status' => NotificationStatus::Pending,
            'priority' => NotificationPriority::Normal,
            'queued_at' => null,
        ]);

        $urgentNotification = Notification::factory()->create([
            'status' => NotificationStatus::Pending,
            'priority' => NotificationPriority::Urgent,
            'queued_at' => null,
        ]);

        NotificationOutbox::factory()->create([
            'notification_id' => $normalNotification->id,
            'priority' => NotificationPriority::Normal,
            'published_at' => null,
        ]);

        NotificationOutbox::factory()->create([
            'notification_id' => $urgentNotification->id,
            'priority' => NotificationPriority::Urgent,
            'published_at' => null,
        ]);

        app(NotificationOutboxService::class)->publishPending(1);

        Queue::assertPushed(SendNotificationJob::class, function (SendNotificationJob $job) use ($urgentNotification): bool {
            return $job->notificationId === $urgentNotification->id;
        });

        Queue::assertPushed(SendNotificationJob::class, 1);

        $normalNotification->refresh();
        $urgentNotification->refresh();

        $this->assertSame(NotificationStatus::Pending, $normalNotification->status);
        $this->assertSame(NotificationStatus::Queued, $urgentNotification->status);
    }
}
