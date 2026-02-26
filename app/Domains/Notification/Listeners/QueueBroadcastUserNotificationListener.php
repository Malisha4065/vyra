<?php

namespace App\Domains\Notification\Listeners;

use App\Domains\Notification\Events\UserNotificationCreated;
use App\Domains\Notification\Jobs\BroadcastUserNotificationJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueueBroadcastUserNotificationListener implements ShouldQueue
{
    public string $queue = 'default';

    public function handle(UserNotificationCreated $event): void
    {
        BroadcastUserNotificationJob::dispatch($event->notification->id)->onQueue('notifications');
    }
}
