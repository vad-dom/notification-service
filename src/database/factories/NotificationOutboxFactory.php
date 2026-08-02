<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\NotificationOutbox;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationOutbox>
 */
class NotificationOutboxFactory extends Factory
{
    protected $model = NotificationOutbox::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'notification_id' => Notification::factory(),
            'queue_name' => 'notifications.critical',
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'published_at' => now(),
        ]);
    }
}
