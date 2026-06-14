<?php

namespace App\Services;

use App\Enums\NotificationChannel;
use App\Interfaces\NotificationProviderInterface;
use App\Models\Notification;
use App\Services\NotificationProviders\EmailProviderMock;
use App\Services\NotificationProviders\SmsProviderMock;
use InvalidArgumentException;

class NotificationProviderResolver
{
    public function resolve(Notification $notification): NotificationProviderInterface
    {
        return match ($notification->batch->channel) {
            NotificationChannel::Sms => app(SmsProviderMock::class),
            NotificationChannel::Email => app(EmailProviderMock::class),
            default => throw new InvalidArgumentException('Unsupported notification channel.'),
        };
    }
}
