<?php

namespace Tests\Feature;

use App\Enums\NotificationStatus;
use App\Models\Notification;
use App\Models\Recipient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class NotificationBatchApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_requires_api_token(): void
    {
        $response = $this->postJson('/api/notification-batches', [
            'channel' => 'sms',
            'type' => 'transactional',
            'message' => 'Your code: 1234',
            'recipient_ids' => [1, 2],
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_store_requires_idempotency_key(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer test-token',
        ])->postJson('/api/notification-batches', [
            'channel' => 'sms',
            'type' => 'transactional',
            'message' => 'Your code: 1234',
            'recipient_ids' => [1, 2],
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_store_creates_notification_batch(): void
    {
        Queue::fake();

        $recipients = Recipient::factory()
            ->count(2)
            ->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer test-token',
            'Idempotency-Key' => '550e8400-e29b-41d4-a716-446655440100',
        ])->postJson('/api/notification-batches', [
            'channel' => 'sms',
            'type' => 'transactional',
            'message' => 'Your code: 1234',
            'recipient_ids' => $recipients->pluck('id')->all(),
        ]);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonPath('data.status', 'accepted')
            ->assertJsonPath('data.notifications_count', 2);

        $this->assertDatabaseCount('notification_batches', 1);
        $this->assertDatabaseCount('notifications', 2);

        Notification::query()
            ->get()
            ->each(function (Notification $notification): void {
                $this->assertSame(NotificationStatus::Queued, $notification->status);
                $this->assertNotNull($notification->queued_at);
            });
    }

    public function test_store_is_idempotent(): void
    {
        Queue::fake();

        $recipients = Recipient::factory()
            ->count(2)
            ->create();

        $payload = [
            'channel' => 'sms',
            'type' => 'transactional',
            'message' => 'Your code: 1234',
            'recipient_ids' => $recipients->pluck('id')->all(),
        ];

        $headers = [
            'Authorization' => 'Bearer test-token',
            'Idempotency-Key' => '550e8400-e29b-41d4-a716-446655440101',
        ];

        $firstResponse = $this->withHeaders($headers)
            ->postJson('/api/notification-batches', $payload);

        $secondResponse = $this->withHeaders($headers)
            ->postJson('/api/notification-batches', $payload);

        $firstResponse->assertStatus(Response::HTTP_CREATED);
        $secondResponse->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseCount('notification_batches', 1);
        $this->assertDatabaseCount('notifications', 2);
    }

    public function test_store_validates_recipients_exist(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer test-token',
            'Idempotency-Key' => '550e8400-e29b-41d4-a716-446655440102',
        ])->postJson('/api/notification-batches', [
            'channel' => 'sms',
            'type' => 'transactional',
            'message' => 'Your code: 1234',
            'recipient_ids' => [999999],
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'recipient_ids.0',
        ]);
    }

    public function test_store_is_rate_limited_by_api_token(): void
    {
        $key = 'notification-batches:create:api-token:'.sha1('test-token');

        RateLimiter::clear($key);

        $payload = [
            'channel' => 'sms',
            'type' => 'transactional',
            'message' => 'Your code: 1234',
            'recipient_ids' => [1],
        ];

        for ($i = 1; $i <= 10; $i++) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer test-token',
                'Idempotency-Key' =>
                    '550e8400-e29b-41d4-a716-4466554402'.str_pad(
                        (string) $i, 2, '0', STR_PAD_LEFT
                    ),
            ])->postJson('/api/notification-batches', $payload);

            $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $response = $this->withHeaders([
            'Authorization' => 'Bearer test-token',
            'Idempotency-Key' => '550e8400-e29b-41d4-a716-446655440299',
        ])->postJson('/api/notification-batches', $payload);

        $response->assertStatus(Response::HTTP_TOO_MANY_REQUESTS);
    }
}
