<?php

namespace App\Domains\Content\Listeners;

use App\Domains\Content\Events\PostReactionAdded;
use App\Domains\Content\Jobs\DispatchPostReactionNotificationJob;
use App\Domains\Content\Jobs\UpdatePostReactionSearchIndexJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueuePostReactionAddedSideEffectsListener implements ShouldQueue
{
    public string $queue = 'default';

    public function handle(PostReactionAdded $event): void
    {
        DispatchPostReactionNotificationJob::dispatch(
            postId: $event->post->id,
            reactorId: $event->reactor->id,
            reactionType: $event->reaction->type,
        )->onQueue('notifications');

        UpdatePostReactionSearchIndexJob::dispatch(
            postId: $event->post->id,
            reactorId: $event->reactor->id,
            reactionType: $event->reaction->type,
            action: 'added',
        )->onQueue('search');
    }
}
