<?php

namespace App\Services;

use App\DTO\ProviderDeliveryStatusData;
use App\Enums\NotificationStatus;
use App\Exceptions\UnexpectedNotificationStatusException;
use App\Models\Notification;

class ProviderEventService
{
    public function updateDeliveryStatus(ProviderDeliveryStatusData $data): Notification
    {
        $notification = Notification::query()
            ->where('provider_message_id', $data->providerMessageId)
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

        $providerStatus = $data->status;
        $notificationStatus = $providerStatus->toNotificationStatus();

        $notification->update([
            'status' => $notificationStatus,
            'delivered_at' => $notificationStatus === NotificationStatus::Delivered ? now() : null,
            'failure_reason' => $notificationStatus === NotificationStatus::Discarded
                ? $data->failureReason ?? 'Provider rejected notification.'
                : null,
        ]);

        return $notification;
    }
}
