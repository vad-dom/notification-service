<?php

namespace App\Models;

use App\Enums\NotificationPriority;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationOutbox extends Model
{
    use HasFactory;

    protected $table = 'notification_outbox';

    protected $fillable = [
        'notification_id',
        'queue_name',
        'priority',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'priority' => NotificationPriority::class,
            'published_at' => 'datetime',
        ];
    }

    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null;
    }
}
