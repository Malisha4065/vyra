<?php

namespace App\Domains\Content\Listeners;

use App\Domains\Content\Events\PostUpdated;
use App\Domains\Content\Jobs\UpdatePublishedPostSearchIndexJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueuePostUpdatedSideEffectsListener implements ShouldQueue
{
    public string $queue = 'default';

    public function handle(PostUpdated $event): void
    {
        UpdatePublishedPostSearchIndexJob::dispatch($event->post->id)->onQueue('search');
    }
}
