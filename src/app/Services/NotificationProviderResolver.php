<?php

namespace App\Services;

use App\Interfaces\NotificationProviderInterface;
use App\Models\Notification;
use Illuminate\Contracts\Foundation\Application;
use InvalidArgumentException;

readonly class NotificationProviderResolver
{
    public function __construct(
        private Application $app,
    ) {}

    public function resolve(Notification $notification): NotificationProviderInterface
    {
        $providerClass = config('notifications.providers.'.$notification->batch->channel->value);

        if ($providerClass === null) {
            throw new InvalidArgumentException('Unsupported notification channel.');
        }

        $provider = $this->app->make($providerClass);

        if (! $provider instanceof NotificationProviderInterface) {
            throw new InvalidArgumentException('Unsupported notification channel.');
        }

        return $provider;
    }
}
