<?php

use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\NotificationHistoryController;
use App\Http\Controllers\Api\ProviderEventController;
use App\Http\Middleware\EnsureApiToken;
use App\Http\Middleware\EnsureIdempotencyKey;
use App\Http\Middleware\EnsureNotificationBatchRateLimit;
use App\Http\Middleware\EnsureProviderToken;
use Illuminate\Support\Facades\Route;

Route::post('/notification-batches', [NotificationController::class, 'store'])
    ->middleware([
        EnsureApiToken::class,
        EnsureNotificationBatchRateLimit::class,
        EnsureIdempotencyKey::class,
    ]);

Route::post('/provider-events/delivery-status', [ProviderEventController::class, 'deliveryStatus'])
    ->middleware(EnsureProviderToken::class);

Route::get('/recipients/{recipient}/notifications', [NotificationHistoryController::class, 'index'])
    ->middleware(EnsureApiToken::class);
