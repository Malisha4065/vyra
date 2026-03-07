<?php

namespace App\Domains\Identity\Listeners;

use App\Domains\Identity\Events\ProfileUpdated;
use App\Domains\Identity\Jobs\UpdateUserSearchIndexJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueueUserSearchIndexListener implements ShouldQueue
{
    public string $queue = 'default';

    public function handle(ProfileUpdated $event): void
    {
        UpdateUserSearchIndexJob::dispatch($event->profile->user_id)->onQueue('search');
    }
}
