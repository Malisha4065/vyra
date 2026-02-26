<?php

namespace App\Domains\SocialGraph\Jobs;

use App\Domains\Notification\Actions\CreateUserNotificationAction;
use App\Domains\Notification\Data\CreateUserNotificationData;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DispatchUserBlockedNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $blockerId,
        public readonly string $blockedId,
    ) {}

    public function handle(CreateUserNotificationAction $createNotificationAction): void
    {
        $createNotificationAction(CreateUserNotificationData::from([
            'user_id' => $this->blockerId,
            'type' => 'social.user_blocked',
            'title' => 'User blocked',
            'body' => 'You blocked a user.',
            'data' => [
                'blocked_user_id' => $this->blockedId,
            ],
        ]));
    }
}
