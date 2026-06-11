<?php

use App\Http\Controllers\Api\NotificationController;
use App\Http\Middleware\EnsureIdempotencyKey;
use Illuminate\Support\Facades\Route;

Route::post('/notification-batches', [NotificationController::class, 'store'])
    ->middleware(EnsureIdempotencyKey::class);
