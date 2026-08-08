<?php

namespace Tests\Feature;

use App\Enums\NotificationPriority;
use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use App\Jobs\SendNotificationJob;
use App\Models\Notification;
use App\Models\NotificationBatch;
use App\Services\NotificationPublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotificationPublisherTest extends TestCase
{
    use RefreshDatabase;

    public function test_publisher_dispatches_send_notification_job(): void
    {
        Queue::fake();

        $notification = Notification::factory()->create([
            'status' => NotificationStatus::Pending,
        ]);

        app(NotificationPublisher::class)->publish($notification);

        Queue::assertPushed(SendNotificationJob::class, function (SendNotificationJob $job) use ($notification): bool {
            return $job->notificationId === $notification->id;
        });
    }

    public function test_publisher_uses_queue_from_notification_type(): void
    {
        Queue::fake();

        $notification = Notification::factory()->create([
            'status' => NotificationStatus::Pending,
        ]);

        app(NotificationPublisher::class)->publish($notification);

        Queue::assertPushedOn('notifications.critical', SendNotificationJob::class);
    }

    public function test_publisher_uses_default_queue_for_marketing_notifications(): void
    {
        Queue::fake();

        $notification = Notification::factory()->create([
            'status' => NotificationStatus::Pending,
            'notification_batch_id' => NotificationBatch::factory()->create([
                'type' => NotificationType::Marketing,
            ]),
        ]);

        app(NotificationPublisher::class)->publish($notification);

        Queue::assertPushedOn('notifications.default', SendNotificationJob::class);
    }

    public function test_publisher_dispatches_job_with_notification_priority(): void
    {
        Queue::fake();

        $notification = Notification::factory()->create([
            'status' => NotificationStatus::Pending,
            'priority' => NotificationPriority::Urgent,
        ]);

        app(NotificationPublisher::class)->publish($notification);

        Queue::assertPushed(SendNotificationJob::class, function (SendNotificationJob $job): bool {
            return $job->priority === NotificationPriority::Urgent->value;
        });
    }
}
