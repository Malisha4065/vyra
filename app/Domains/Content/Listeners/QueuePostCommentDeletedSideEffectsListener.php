<?php

namespace App\Domains\Content\Listeners;

use App\Domains\Content\Events\PostCommentDeleted;
use App\Domains\Content\Jobs\UpdatePostCommentSearchIndexJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueuePostCommentDeletedSideEffectsListener implements ShouldQueue
{
    public string $queue = 'default';

    public function handle(PostCommentDeleted $event): void
    {
        UpdatePostCommentSearchIndexJob::dispatch(
            postId: $event->postId,
            commentId: $event->commentId,
            action: 'deleted',
        )->onQueue('search');
    }
}
