<?php

namespace App\Enums;

enum NotificationPriority: int
{
    case Marketing = 1;
    case Transactional = 10;
}
