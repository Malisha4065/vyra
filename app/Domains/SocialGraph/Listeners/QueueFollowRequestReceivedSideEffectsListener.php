<?php

namespace App\Domains\SocialGraph\Listeners;

use App\Domains\SocialGraph\Events\FollowRequestReceived;
use App\Domains\SocialGraph\Jobs\DispatchFollowRequestReceivedNotificationJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueueFollowRequestReceivedSideEffectsListener implements ShouldQueue
{
    public string $queue = 'default';

    public function handle(FollowRequestReceived $event): void
    {
        DispatchFollowRequestReceivedNotificationJob::dispatch(
            requestId: $event->followRequest->id,
            requesterId: $event->followRequest->requester_id,
            requesteeId: $event->followRequest->requestee_id,
        )->onQueue('notifications');
    }
}
