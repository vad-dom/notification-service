<?php

namespace Database\Factories;

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Models\NotificationBatch;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<NotificationBatch>
 */
class NotificationBatchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'channel' => NotificationChannel::Sms,
            'type' => NotificationType::Transactional,
            'message' => fake()->sentence(),
            'idempotency_key' => (string) Str::uuid(),
        ];
    }
}
