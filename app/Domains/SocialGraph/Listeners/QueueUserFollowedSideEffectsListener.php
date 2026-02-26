<?php

namespace App\Domains\SocialGraph\Listeners;

use App\Domains\SocialGraph\Events\UserFollowed;
use App\Domains\SocialGraph\Jobs\DispatchUserFollowedNotificationJob;
use App\Domains\SocialGraph\Jobs\UpdateFollowGraphSearchProjectionJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueueUserFollowedSideEffectsListener implements ShouldQueue
{
    public string $queue = 'default';

    public function handle(UserFollowed $event): void
    {
        DispatchUserFollowedNotificationJob::dispatch(
            followerId: $event->follower->id,
            followeeId: $event->followee->id,
        )->onQueue('notifications');

        UpdateFollowGraphSearchProjectionJob::dispatch(
            sourceUserId: $event->follower->id,
            targetUserId: $event->followee->id,
            action: 'followed',
        )->onQueue('search');
    }
}
