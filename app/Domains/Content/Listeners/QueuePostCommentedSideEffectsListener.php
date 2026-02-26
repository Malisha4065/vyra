<?php

namespace App\Domains\Content\Listeners;

use App\Domains\Content\Events\PostCommented;
use App\Domains\Content\Jobs\DispatchPostCommentNotificationJob;
use App\Domains\Content\Jobs\UpdatePostCommentSearchIndexJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueuePostCommentedSideEffectsListener implements ShouldQueue
{
    public string $queue = 'default';

    public function handle(PostCommented $event): void
    {
        DispatchPostCommentNotificationJob::dispatch(
            commentId: $event->comment->id,
            postId: $event->post->id,
            authorId: $event->author->id,
        )->onQueue('notifications');

        UpdatePostCommentSearchIndexJob::dispatch(
            postId: $event->post->id,
            commentId: $event->comment->id,
            action: 'commented',
        )->onQueue('search');
    }
}
