<?php

namespace App\Domains\SocialGraph\Jobs;

use App\Domains\Notification\Actions\CreateUserNotificationAction;
use App\Domains\Notification\Data\CreateUserNotificationData;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DispatchUserFollowedNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $followerId,
        public readonly string $followeeId,
    ) {}

    public function handle(CreateUserNotificationAction $createNotificationAction): void
    {
        $createNotificationAction(CreateUserNotificationData::from([
            'user_id' => $this->followeeId,
            'type' => 'social.followed',
            'title' => 'New follower',
            'body' => 'Someone started following you.',
            'data' => [
                'actor_user_id' => $this->followerId,
                'target_user_id' => $this->followeeId,
            ],
        ]));
    }
}
