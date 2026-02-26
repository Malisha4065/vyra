<?php

namespace App\Domains\SocialGraph\Listeners;

use App\Domains\SocialGraph\Events\FollowRequestAccepted;
use App\Domains\SocialGraph\Jobs\DispatchFollowRequestAcceptedNotificationJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueueFollowRequestAcceptedSideEffectsListener implements ShouldQueue
{
    public string $queue = 'default';

    public function handle(FollowRequestAccepted $event): void
    {
        DispatchFollowRequestAcceptedNotificationJob::dispatch(
            requestId: $event->followRequest->id,
            requesterId: $event->followRequest->requester_id,
            requesteeId: $event->followRequest->requestee_id,
        )->onQueue('notifications');
    }
}
