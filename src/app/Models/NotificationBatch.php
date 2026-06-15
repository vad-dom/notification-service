<?php

namespace App\Models;

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel',
        'type',
        'message',
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'channel' => NotificationChannel::class,
            'type' => NotificationType::class,
        ];
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
