<?php

namespace App\DTO;

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use InvalidArgumentException;

readonly class CreateNotificationBatchData
{
    /**
     * @param  list<int>  $recipientIds
     */
    public function __construct(
        public NotificationChannel $channel,
        public NotificationType $type,
        public string $message,
        public array $recipientIds,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        if (! is_array($data['recipient_ids'] ?? null)) {
            throw new InvalidArgumentException('recipient_ids must be an array.');
        }

        /** @var list<int> $recipientIds */
        $recipientIds = array_values(array_map(
            static fn (mixed $recipientId): int => (int) $recipientId,
            $data['recipient_ids'],
        ));

        return new self(
            channel: NotificationChannel::from($data['channel']),
            type: NotificationType::from($data['type']),
            message: $data['message'],
            recipientIds: $recipientIds,
        );
    }
}
