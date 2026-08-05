<?php

namespace App\Services\NotificationProviders;

use App\Interfaces\NotificationProviderInterface;
use App\Models\Notification;
use InvalidArgumentException;

class EmailProviderMock implements NotificationProviderInterface
{
    public function send(Notification $notification): string
    {
        $email = $notification->recipient?->email;

        if ($email === null || $email === '') {
            throw new InvalidArgumentException('Recipient email is required to send email.');
        }

        return 'email-'.$notification->id;
    }
}
