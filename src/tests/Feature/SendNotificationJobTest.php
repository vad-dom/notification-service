<?php

namespace Tests\Feature;

use App\Enums\NotificationStatus;
use App\Jobs\SendNotificationJob;
use App\Models\Notification;
use App\Services\NotificationProviderResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
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

    public function test_job_marks_reconciliation_pending_notification_as_sent(): void
    {
        $notification = Notification::factory()->create([
            'status' => NotificationStatus::ReconciliationPending,
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
    }

    public function test_failed_marks_reconciliation_pending_notification_as_failed(): void
    {
        $notification = Notification::factory()->create([
            'status' => NotificationStatus::ReconciliationPending,
            'failure_reason' => null,
        ]);

        $job = new SendNotificationJob($notification->id);
        $job->failed(new RuntimeException('Provider timeout'));

        $notification->refresh();

        $this->assertSame(NotificationStatus::Failed, $notification->status);
        $this->assertSame('Provider timeout', $notification->failure_reason);
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

    public function test_job_skips_processing_when_lock_is_already_held(): void
    {
        $notification = Notification::factory()->create([
            'status' => NotificationStatus::Queued,
            'provider_message_id' => null,
            'sent_at' => null,
        ]);

        $lock = Cache::lock("notification:{$notification->id}", 30);

        $this->assertTrue($lock->get());

        try {
            (new SendNotificationJob($notification->id))->handle(
                app(NotificationProviderResolver::class)
            );

            $notification->refresh();

            $this->assertSame(NotificationStatus::Queued, $notification->status);
            $this->assertNull($notification->provider_message_id);
            $this->assertNull($notification->sent_at);
        } finally {
            $lock->release();
        }
    }

    public function test_failed_marks_queued_notification_as_failed(): void
    {
        $notification = Notification::factory()->create([
            'status' => NotificationStatus::Queued,
            'failure_reason' => null,
        ]);

        $job = new SendNotificationJob($notification->id);
        $job->failed(new RuntimeException('Provider timeout'));

        $notification->refresh();

        $this->assertSame(NotificationStatus::Failed, $notification->status);
        $this->assertSame('Provider timeout', $notification->failure_reason);
    }

    public function test_failed_does_not_overwrite_terminal_status(): void
    {
        $notification = Notification::factory()->create([
            'status' => NotificationStatus::Sent,
            'provider_message_id' => 'sms-existing',
            'failure_reason' => null,
        ]);

        $job = new SendNotificationJob($notification->id);
        $job->failed(new RuntimeException('Provider timeout'));

        $notification->refresh();

        $this->assertSame(NotificationStatus::Sent, $notification->status);
        $this->assertNull($notification->failure_reason);
    }
}
