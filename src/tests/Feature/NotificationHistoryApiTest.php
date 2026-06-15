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
    }
}
