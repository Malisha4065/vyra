<?php

namespace App\Domains\SocialGraph\Listeners;

use App\Domains\SocialGraph\Events\UserUnfollowed;
use App\Domains\SocialGraph\Jobs\UpdateFollowGraphSearchProjectionJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueueUserUnfollowedSideEffectsListener implements ShouldQueue
{
    public string $queue = 'default';

    public function handle(UserUnfollowed $event): void
    {
        UpdateFollowGraphSearchProjectionJob::dispatch(
            sourceUserId: $event->follower->id,
            targetUserId: $event->followee->id,
            action: 'unfollowed',
        )->onQueue('search');
    }
}
