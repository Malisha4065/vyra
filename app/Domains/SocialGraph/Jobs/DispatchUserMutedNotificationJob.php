<?php

namespace App\Domains\SocialGraph\Jobs;

use App\Domains\Notification\Actions\CreateUserNotificationAction;
use App\Domains\Notification\Data\CreateUserNotificationData;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DispatchUserMutedNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $muterId,
        public readonly string $mutedId,
    ) {}

    public function handle(CreateUserNotificationAction $createNotificationAction): void
    {
        $createNotificationAction(CreateUserNotificationData::from([
            'user_id' => $this->muterId,
            'type' => 'social.user_muted',
            'title' => 'User muted',
            'body' => 'You muted a user.',
            'data' => [
                'muted_user_id' => $this->mutedId,
            ],
        ]));
    }
}
