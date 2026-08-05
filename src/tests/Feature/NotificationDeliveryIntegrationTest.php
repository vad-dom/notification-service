<?php

namespace Tests\Feature;

use App\Enums\NotificationStatus;
use App\Models\Notification;
use App\Models\NotificationOutbox;
use App\Models\Recipient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class NotificationDeliveryIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['queue.default' => 'database']);
    }

    public function test_notification_pipeline_from_api_through_queue_to_provider_and_webhook(): void
    {
        $recipients = Recipient::factory()
            ->count(2)
            ->create();

        $createResponse = $this->withHeaders([
            'Authorization' => 'Bearer test-token',
            'Idempotency-Key' => '550e8400-e29b-41d4-a716-446655441000',
        ])->postJson('/api/notification-batches', [
            'channel' => 'sms',
            'type' => 'transactional',
            'message' => 'Your code: 1234',
            'recipient_ids' => $recipients->pluck('id')->all(),
        ]);

        $createResponse->assertStatus(Response::HTTP_CREATED)
            ->assertJsonPath('data.status', 'accepted')
            ->assertJsonPath('data.notifications_count', 2);

        $this->artisan('queue:work', [
            '--stop-when-empty' => true,
            '--queue' => 'notifications.outbox,notifications.critical,notifications.default',
        ])->assertSuccessful();

        $notifications = Notification::query()
            ->orderBy('id')
            ->get();

        $this->assertCount(2, $notifications);

        foreach ($notifications as $notification) {
            $this->assertSame(NotificationStatus::Sent, $notification->status);
            $this->assertSame('sms-'.$notification->id, $notification->provider_message_id);
            $this->assertNotNull($notification->queued_at);
            $this->assertNotNull($notification->sent_at);
            $this->assertNull($notification->delivered_at);
        }

        NotificationOutbox::query()
            ->get()
            ->each(fn (NotificationOutbox $entry) => $this->assertNotNull($entry->published_at));

        $notification = $notifications->first();

        $webhookResponse = $this->withHeaders([
            'X-Provider-Token' => 'super-secret-token',
        ])->postJson('/api/provider-events/delivery-status', [
            'provider_message_id' => $notification->provider_message_id,
            'status' => 'delivered',
        ]);

        $webhookResponse->assertOk()
            ->assertJsonPath('data.id', $notification->id)
            ->assertJsonPath('data.status', 'delivered');

        $notification->refresh();

        $this->assertSame(NotificationStatus::Delivered, $notification->status);
        $this->assertNotNull($notification->delivered_at);

        $this->assertSame(
            NotificationStatus::Sent,
            $notifications->last()->fresh()->status,
        );
    }
}
