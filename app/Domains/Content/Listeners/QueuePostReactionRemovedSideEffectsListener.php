<?php

namespace App\Domains\Content\Listeners;

use App\Domains\Content\Events\PostReactionRemoved;
use App\Domains\Content\Jobs\UpdatePostReactionSearchIndexJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueuePostReactionRemovedSideEffectsListener implements ShouldQueue
{
    public string $queue = 'default';

    public function handle(PostReactionRemoved $event): void
    {
        UpdatePostReactionSearchIndexJob::dispatch(
            postId: $event->post->id,
            reactorId: $event->reactor->id,
            reactionType: $event->type,
            action: 'removed',
        )->onQueue('search');
    }
}
