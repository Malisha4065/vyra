<?php

namespace App\Domains\Identity\Listeners;

use App\Domains\Identity\Events\UserRegistered;
use App\Domains\Identity\Jobs\UpdateUserSearchIndexJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueueRegisteredUserSearchIndexListener implements ShouldQueue
{
    public string $queue = 'default';

    public function handle(UserRegistered $event): void
    {
        UpdateUserSearchIndexJob::dispatch($event->user->id)->onQueue('search');
    }
}
