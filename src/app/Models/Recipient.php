<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'email',
        'phone',
    ];

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
