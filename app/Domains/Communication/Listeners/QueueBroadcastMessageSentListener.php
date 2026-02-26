<?php

namespace App\Domains\Communication\Listeners;

use App\Domains\Communication\Events\MessageSent;
use App\Domains\Communication\Jobs\BroadcastMessageSentJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueueBroadcastMessageSentListener implements ShouldQueue
{
    public string $queue = 'default';

    public function handle(MessageSent $event): void
    {
        BroadcastMessageSentJob::dispatch($event->message->id)->onQueue('communication');
    }
}
