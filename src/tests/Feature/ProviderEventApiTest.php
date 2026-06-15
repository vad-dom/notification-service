<?php

namespace Tests\Feature;

use App\Enums\NotificationStatus;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ProviderEventApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivery_status_requires_provider_token(): void
    {
        $notification = Notification::factory()->create([
            'status' => NotificationStatus::Sent,
            'provider_message_id' => 'sms-100',
            'sent_at' => now(),
        ]);

        $response = $this->postJson('/api/provider-events/delivery-status', [
            'provider_message_id' => $notification->provider_message_id,
            'status' => 'delivered',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_delivery_status_marks_notification_as_delivered(): void
    {
        $notification = Notification::factory()->create([
            'status' => NotificationStatus::Sent,
            'provider_message_id' => 'sms-101',
            'sent_at' => now(),
        ]);

        $response = $this->withHeaders([
            'X-Provider-Token' => 'super-secret-token',
        ])->postJson('/api/provider-events/delivery-status', [
            'provider_message_id' => $notification->provider_message_id,
            'status' => 'delivered',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.id', $notification->id)
            ->assertJsonPath('data.status', 'delivered');

        $notification->refresh();

        $this->assertSame(NotificationStatus::Delivered, $notification->status);
        $this->assertNotNull($notification->delivered_at);
        $this->assertNull($notification->failure_reason);
    }

    public function test_delivery_status_marks_notification_as_discarded(): void
    {
        $notification = Notification::factory()->create([
            'status' => NotificationStatus::Sent,
            'provider_message_id' => 'sms-102',
            'sent_at' => now(),
        ]);

        $response = $this->withHeaders([
            'X-Provider-Token' => 'super-secret-token',
        ])->postJson('/api/provider-events/delivery-status', [
            'provider_message_id' => $notification->provider_message_id,
            'status' => 'discarded',
            'failure_reason' => 'Invalid phone number.',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.id', $notification->id)
            ->assertJsonPath('data.status', 'discarded');

        $notification->refresh();

        $this->assertSame(NotificationStatus::Discarded, $notification->status);
        $this->assertNull($notification->delivered_at);
        $this->assertSame('Invalid phone number.', $notification->failure_reason);
    }

    public function test_delivery_status_rejects_already_processed_notification(): void
    {
        $notification = Notification::factory()->create([
            'status' => NotificationStatus::Delivered,
            'provider_message_id' => 'sms-103',
            'sent_at' => now(),
            'delivered_at' => now(),
        ]);

        $response = $this->withHeaders([
            'X-Provider-Token' => 'super-secret-token',
        ])->postJson('/api/provider-events/delivery-status', [
            'provider_message_id' => $notification->provider_message_id,
            'status' => 'delivered',
        ]);

        $response->assertStatus(Response::HTTP_CONFLICT);
    }
}
