<?php

namespace App\Domains\Notification\Jobs;

use App\Domains\Notification\Actions\BuildUserNotificationPayloadAction;
use App\Domains\Notification\Events\Broadcast\UserNotificationBroadcast;
use App\Domains\Notification\Repositories\UserNotificationRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class BroadcastUserNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $notificationId,
    ) {}

    public function handle(
        UserNotificationRepositoryInterface $notificationRepository,
        BuildUserNotificationPayloadAction $buildPayload,
    ): void
    {
        $notification = $notificationRepository->findById($this->notificationId);

        if ($notification === null) {
            return;
        }

        event(new UserNotificationBroadcast(
            userId: $notification->user_id,
            notification: $buildPayload($notification),
        ));
    }
}
