<?php

namespace App\Domains\Content\Listeners;

use App\Domains\Content\Events\PostDeleted;
use App\Domains\Content\Jobs\RemoveDeletedPostFromFeedCachesJob;
use App\Domains\Content\Jobs\RemoveDeletedPostFromSearchIndexJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueuePostDeletedSideEffectsListener implements ShouldQueue
{
    public string $queue = 'default';

    public function handle(PostDeleted $event): void
    {
        RemoveDeletedPostFromSearchIndexJob::dispatch($event->postId)->onQueue('search');
        RemoveDeletedPostFromFeedCachesJob::dispatch($event->postId, $event->authorId)->onQueue('feed');
    }
}
