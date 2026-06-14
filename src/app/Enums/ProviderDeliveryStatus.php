<?php

namespace App\Enums;

enum ProviderDeliveryStatus: string
{
    case Delivered = 'delivered';
    case Discarded = 'discarded';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function toNotificationStatus(): NotificationStatus
    {
        return match ($this) {
            self::Delivered => NotificationStatus::Delivered,
            self::Discarded => NotificationStatus::Discarded,
        };
    }
}
