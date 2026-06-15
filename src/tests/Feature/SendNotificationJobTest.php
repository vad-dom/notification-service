<?php

namespace Tests\Feature;

use App\Enums\NotificationStatus;
use App\Jobs\SendNotificationJob;
use App\Models\Notification;
use App\Services\NotificationProviderResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SendNotificationJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_marks_queued_notification_as_sent(): void
    {
        $notification = Notification::factory()->create([
            'status' => NotificationStatus::Queued,
            'provider_message_id' => null,
            'sent_at' => null,
        ]);

        (new SendNotificationJob($notification->id))->handle(
            app(NotificationProviderResolver::class)
        );

        $notification->refresh();

        $this->assertSame(NotificationStatus::Sent, $notification->status);
        $this->assertSame('sms-'.$notification->id, $notification->provider_message_id);
        $this->assertNotNull($notification->sent_at);
        $this->assertNull($notification->delivered_at);
    }

    public function test_job_does_not_send_notification_if_status_is_not_queued(): void
    {
        $notification = Notification::factory()->create([
            'status' => NotificationStatus::Sent,
            'provider_message_id' => 'sms-existing',
            'sent_at' => now(),
        ]);

        (new SendNotificationJob($notification->id))->handle(
            app(NotificationProviderResolver::class)
        );

        $notification->refresh();

        $this->assertSame(NotificationStatus::Sent, $notification->status);
        $this->assertSame('sms-existing', $notification->provider_message_id);
    }
}
