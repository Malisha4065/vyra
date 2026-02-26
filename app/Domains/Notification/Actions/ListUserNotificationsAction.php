<?php

namespace App\Domains\Notification\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Notification\Data\ListUserNotificationsData;
use App\Domains\Notification\Repositories\UserNotificationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListUserNotificationsAction
{
    public function __construct(
        private readonly UserNotificationRepositoryInterface $notificationRepository,
    ) {}

    public function __invoke(User $user, ListUserNotificationsData $data): LengthAwarePaginator
    {
        return $this->notificationRepository->paginateForUser(
            userId: $user->id,
            perPage: $data->per_page,
            unreadOnly: $data->unread_only,
        );
    }
}
