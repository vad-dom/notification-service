<?php

namespace App\Services\NotificationProviders;

use App\Interfaces\NotificationProviderInterface;
use App\Models\Notification;

class EmailProviderMock implements NotificationProviderInterface
{
    public function send(Notification $notification): string
    {
        return 'email-'.$notification->id;
    }
}
