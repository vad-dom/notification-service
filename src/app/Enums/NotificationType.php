<?php

namespace App\Enums;

enum NotificationType: string
{
    case Transactional = 'transactional';
    case Marketing = 'marketing';

    public function priority(): NotificationPriority
    {
        return match ($this) {
            self::Transactional => NotificationPriority::Urgent,
            self::Marketing => NotificationPriority::Normal,
        };
    }

    public function queueName(): string
    {
        return match ($this) {
            self::Transactional => 'notifications.critical',
            self::Marketing => 'notifications.default',
        };
    }
}
