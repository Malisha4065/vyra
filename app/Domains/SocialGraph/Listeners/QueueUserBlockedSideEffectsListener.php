<?php

namespace App\Domains\SocialGraph\Listeners;

use App\Domains\SocialGraph\Events\UserBlocked;
use App\Domains\SocialGraph\Jobs\DispatchUserBlockedNotificationJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueueUserBlockedSideEffectsListener implements ShouldQueue
{
    public string $queue = 'default';

    public function handle(UserBlocked $event): void
    {
        DispatchUserBlockedNotificationJob::dispatch(
            blockerId: $event->blocker->id,
            blockedId: $event->blocked->id,
        )->onQueue('notifications');
    }
}
