<?php

namespace App\Domains\Content\Listeners;

use App\Domains\Content\Events\PostPublished;
use App\Domains\Content\Jobs\ProcessPublishedPostMediaJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueuePostMediaProcessingListener implements ShouldQueue
{
    public string $queue = 'default';

    public function handle(PostPublished $event): void
    {
        ProcessPublishedPostMediaJob::dispatch($event->post->id)->onQueue('media');
    }
}
