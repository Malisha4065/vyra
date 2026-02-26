<?php

namespace App\Domains\Content\Listeners;

use App\Domains\Content\Events\PostPublished;
use App\Domains\Content\Jobs\NotifyFollowersOfPublishedPostJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueuePostFanOutListener implements ShouldQueue
{
    public string $queue = 'default';

    public function handle(PostPublished $event): void
    {
        NotifyFollowersOfPublishedPostJob::dispatch(
            postId: $event->post->id,
            authorId: $event->author->id,
        )->onQueue('feed');
    }
}
