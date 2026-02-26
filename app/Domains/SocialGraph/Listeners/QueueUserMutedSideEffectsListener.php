<?php

namespace App\Domains\SocialGraph\Listeners;

use App\Domains\SocialGraph\Events\UserMuted;
use App\Domains\SocialGraph\Jobs\DispatchUserMutedNotificationJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueueUserMutedSideEffectsListener implements ShouldQueue
{
    public string $queue = 'default';

    public function handle(UserMuted $event): void
    {
        DispatchUserMutedNotificationJob::dispatch(
            muterId: $event->muter->id,
            mutedId: $event->muted->id,
        )->onQueue('notifications');
    }
}
