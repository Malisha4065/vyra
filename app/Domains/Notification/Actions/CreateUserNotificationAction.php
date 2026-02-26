<?php

namespace App\Domains\Notification\Actions;

use App\Domains\Notification\Data\CreateUserNotificationData;
use App\Domains\Notification\Events\UserNotificationCreated;
use App\Domains\Notification\Models\UserNotification;
use App\Domains\Notification\Repositories\UserNotificationRepositoryInterface;

class CreateUserNotificationAction
{
    public function __construct(
        private readonly UserNotificationRepositoryInterface $notificationRepository,
    ) {}

    public function __invoke(CreateUserNotificationData $data): UserNotification
    {
        $notification = $this->notificationRepository->create([
            'user_id' => $data->user_id,
            'type' => $data->type,
            'title' => $data->title,
            'body' => $data->body,
            'data' => $data->data,
        ]);

        event(new UserNotificationCreated($notification));

        return $notification;
    }
}
