<?php

namespace App\Domains\Content\Listeners;

use App\Domains\Content\Events\PostPublished;
use App\Domains\Content\Jobs\UpdatePublishedPostSearchIndexJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueuePostSearchIndexListener implements ShouldQueue
{
    public string $queue = 'default';

    public function handle(PostPublished $event): void
    {
        UpdatePublishedPostSearchIndexJob::dispatch($event->post->id)->onQueue('search');
    }
}
