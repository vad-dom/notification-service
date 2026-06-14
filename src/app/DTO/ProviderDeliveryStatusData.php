<?php

namespace App\DTO;

use App\Enums\ProviderDeliveryStatus;

readonly class ProviderDeliveryStatusData
{
    public function __construct(
        public string $providerMessageId,
        public ProviderDeliveryStatus $status,
        public ?string $failureReason,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            providerMessageId: $data['provider_message_id'],
            status: ProviderDeliveryStatus::from($data['status']),
            failureReason: $data['failure_reason'] ?? null,
        );
    }
}
