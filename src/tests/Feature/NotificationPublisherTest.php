<?php

namespace Tests\Feature;

use App\Enums\NotificationStatus;
use App\Jobs\SendNotificationJob;
use App\Models\Notification;
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
}
