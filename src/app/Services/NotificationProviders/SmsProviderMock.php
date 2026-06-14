<?php

namespace App\Services\NotificationProviders;

use App\Interfaces\NotificationProviderInterface;
use App\Models\Notification;

class SmsProviderMock implements NotificationProviderInterface
{
    public function send(Notification $notification): string
    {
        return 'sms-'.$notification->id;
    }
}
