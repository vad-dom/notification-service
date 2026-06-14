<?php

namespace App\Services;

use App\Enums\NotificationStatus;
use App\Enums\ProviderDeliveryStatus;
use App\Exceptions\UnexpectedNotificationStatusException;
use App\Models\Notification;

class ProviderEventService
{
    public function updateDeliveryStatus(array $data): Notification
    {
        $notification = Notification::query()
            ->where('provider_message_id', $data['provider_message_id'])
            ->firstOrFail();

        if ($notification->status !== NotificationStatus::Sent) {
            throw new UnexpectedNotificationStatusException(
                sprintf(
                    'Expected notification status "%s", got "%s".',
                    NotificationStatus::Sent->value,
                    $notification->status->value,
                )
            );
        }

        $providerStatus = ProviderDeliveryStatus::from($data['status']);
        $notificationStatus = $providerStatus->toNotificationStatus();

        $notification->update([
            'status' => $notificationStatus,
            'delivered_at' => $notificationStatus === NotificationStatus::Delivered ? now() : null,
            'failure_reason' => $notificationStatus === NotificationStatus::Discarded
                ? $data['failure_reason'] ?? 'Provider rejected notification.'
                : null,
        ]);

        return $notification;
    }
}
