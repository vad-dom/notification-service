<?php

namespace Database\Factories;

use App\Enums\NotificationPriority;
use App\Enums\NotificationStatus;
use App\Models\Notification;
use App\Models\NotificationBatch;
use App\Models\Recipient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'notification_batch_id' => NotificationBatch::factory(),
            'recipient_id' => Recipient::factory(),
            'status' => NotificationStatus::Queued,
            'priority' => NotificationPriority::Urgent,
            'provider_message_id' => null,
            'failure_reason' => null,
            'queued_at' => now(),
            'sent_at' => null,
            'delivered_at' => null,
        ];
    }
}
