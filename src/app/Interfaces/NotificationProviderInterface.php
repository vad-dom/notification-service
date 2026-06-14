<?php

namespace App\Interfaces;

use App\Models\Notification;

interface NotificationProviderInterface
{
    public function send(Notification $notification): string;
}
