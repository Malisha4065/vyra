<?php

namespace App\Domains\Notification\Jobs;

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

        // Reverb broadcast will be implemented in Communication domain integration.
    }
}
