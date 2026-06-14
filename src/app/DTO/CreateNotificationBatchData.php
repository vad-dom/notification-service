<?php

namespace App\DTO;

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;

readonly class CreateNotificationBatchData
{
    public function __construct(
        public NotificationChannel $channel,
        public NotificationType $type,
        public string $message,
        public array $recipientIds,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            channel: NotificationChannel::from($data['channel']),
            type: NotificationType::from($data['type']),
            message: $data['message'],
            recipientIds: $data['recipient_ids'],
        );
    }
}
