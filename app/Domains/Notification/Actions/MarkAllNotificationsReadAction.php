<?php

namespace App\Domains\Notification\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Notification\Data\MarkAllNotificationsReadData;
use App\Domains\Notification\Repositories\UserNotificationRepositoryInterface;

class MarkAllNotificationsReadAction
{
    public function __construct(
        private readonly UserNotificationRepositoryInterface $notificationRepository,
    ) {}

    public function __invoke(User $user, MarkAllNotificationsReadData $data): int
    {
        return $this->notificationRepository->markAllAsRead($user->id);
    }
}
