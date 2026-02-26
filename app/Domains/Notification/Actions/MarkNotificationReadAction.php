<?php

namespace App\Domains\Notification\Actions;

use App\Domains\Notification\Data\MarkNotificationReadData;
use App\Domains\Notification\Models\UserNotification;
use App\Domains\Notification\Repositories\UserNotificationRepositoryInterface;

class MarkNotificationReadAction
{
    public function __construct(
        private readonly UserNotificationRepositoryInterface $notificationRepository,
    ) {}

    public function __invoke(UserNotification $notification, MarkNotificationReadData $data): UserNotification
    {
        if ($data->notification_id !== $notification->id) {
            return $notification;
        }

        return $this->notificationRepository->markAsRead($notification);
    }
}
