<?php

namespace App\Domains\Identity\Listeners;

use App\Domains\Identity\Events\AccountDeleted;
use App\Domains\Identity\Jobs\RemoveDeletedUserFromSearchIndexJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueueRemoveDeletedUserSearchIndexListener implements ShouldQueue
{
    public string $queue = 'default';

    public function handle(AccountDeleted $event): void
    {
        RemoveDeletedUserFromSearchIndexJob::dispatch(
            userId: $event->userId,
            username: $event->username,
        )->onQueue('search');
    }
}
