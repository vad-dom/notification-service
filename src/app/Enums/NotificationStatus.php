<?php

namespace App\Enums;

enum NotificationStatus: string
{
    case Pending = 'pending';
    case Queued = 'queued';
    case ReconciliationPending = 'reconciliation_pending';
    case Sent = 'sent';
    case Delivered = 'delivered';
    case Failed = 'failed';
    case Discarded = 'discarded';

    public function isSendable(): bool
    {
        return in_array($this, [self::Queued, self::ReconciliationPending], true);
    }
}
