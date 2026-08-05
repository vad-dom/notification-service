<?php

namespace App\Services\NotificationProviders;

use App\Interfaces\NotificationProviderInterface;
use App\Models\Notification;
use InvalidArgumentException;

class SmsProviderMock implements NotificationProviderInterface
{
    public function send(Notification $notification): string
    {
        $phone = $notification->recipient?->phone;

        if ($phone === null || $phone === '') {
            throw new InvalidArgumentException('Recipient phone is required to send SMS.');
        }

        return 'sms-'.$notification->id;
    }
}
