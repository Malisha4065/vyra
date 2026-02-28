<?php

namespace App\Domains\Notification\Jobs;

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

    public function handle(UserNotificationRepositoryInterface $notificationRepository): void
    {
        $notification = $notificationRepository->findById($this->notificationId);

        if ($notification === null) {
            return;
        }

        event(new UserNotificationBroadcast(
            userId: $notification->user_id,
            notification: [
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->title,
                'body' => $notification->body,
                'data' => $notification->data ?? [],
                'read_at' => $notification->read_at?->toIso8601String(),
                'created_at' => $notification->created_at?->toIso8601String(),
            ],
        ));
    }
}
