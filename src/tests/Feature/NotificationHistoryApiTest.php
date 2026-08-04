<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Recipient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationHistoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_recipient_notification_history_returns_notifications(): void
    {
        $recipient = Recipient::factory()->create();

        Notification::factory()
            ->count(2)
            ->create([
                'recipient_id' => $recipient->id,
            ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer test-token',
        ])->getJson(
            "/api/recipients/{$recipient->id}/notifications"
        );

        $response->assertOk();

        $response->assertJsonCount(2, 'data');
        $response->assertJsonStructure([
            'data',
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => ['path', 'per_page', 'next_cursor', 'prev_cursor'],
        ]);
        $response->assertJsonPath('meta.per_page', 15);
    }

    public function test_recipient_notification_history_supports_cursor_pagination(): void
    {
        $recipient = Recipient::factory()->create();

        Notification::factory()
            ->count(20)
            ->create([
                'recipient_id' => $recipient->id,
            ]);

        $firstPageResponse = $this->withHeaders([
            'Authorization' => 'Bearer test-token',
        ])->getJson(
            "/api/recipients/{$recipient->id}/notifications"
        );

        $firstPageResponse->assertOk()
            ->assertJsonCount(15, 'data')
            ->assertJsonPath('meta.per_page', 15);

        $nextCursor = $firstPageResponse->json('meta.next_cursor');

        $this->assertNotNull($nextCursor);
        $this->assertNotNull($firstPageResponse->json('links.next'));

        $secondPageResponse = $this->withHeaders([
            'Authorization' => 'Bearer test-token',
        ])->getJson(
            "/api/recipients/{$recipient->id}/notifications?cursor={$nextCursor}"
        );

        $secondPageResponse->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.per_page', 15);

        $firstPageIds = collect($firstPageResponse->json('data'))->pluck('id');
        $secondPageIds = collect($secondPageResponse->json('data'))->pluck('id');

        $this->assertTrue($firstPageIds->intersect($secondPageIds)->isEmpty());
    }
}
