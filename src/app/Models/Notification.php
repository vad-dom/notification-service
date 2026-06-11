<?php

namespace App\Models;

use App\Enums\NotificationPriority;
use App\Enums\NotificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'notification_batch_id',
        'recipient_id',
        'status',
        'priority',
        'provider_message_id',
        'failure_reason',
        'queued_at',
        'sent_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => NotificationStatus::class,
            'priority' => NotificationPriority::class,
            'queued_at' => 'datetime',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(NotificationBatch::class, 'notification_batch_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(Recipient::class);
    }
}
