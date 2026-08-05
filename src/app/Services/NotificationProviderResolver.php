<?php

namespace App\Services;

use App\Enums\NotificationChannel;
use App\Interfaces\NotificationProviderInterface;
use App\Models\Notification;
use App\Services\NotificationProviders\EmailProviderMock;
use App\Services\NotificationProviders\SmsProviderMock;
use InvalidArgumentException;

readonly class NotificationProviderResolver
{
    public function __construct(
        private SmsProviderMock $smsProvider,
        private EmailProviderMock $emailProvider,
    ) {}

    public function resolve(Notification $notification): NotificationProviderInterface
    {
        return match ($notification->batch->channel) {
            NotificationChannel::Sms => $this->smsProvider,
            NotificationChannel::Email => $this->emailProvider,
            default => throw new InvalidArgumentException('Unsupported notification channel.'),
        };
    }
}
