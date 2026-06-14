<?php

namespace App\Enums;

enum NotificationPriority: int
{
    case Normal = 1;
    case Urgent = 10;
}
