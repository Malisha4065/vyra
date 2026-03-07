<?php

namespace App\Domains\Identity\Listeners;

use App\Domains\Identity\Events\PasswordResetLinkRequested;
use App\Domains\Identity\Jobs\SendPasswordResetLinkJob;

class QueueSendPasswordResetLinkListener
{
    public function handle(PasswordResetLinkRequested $event): void
    {
        SendPasswordResetLinkJob::dispatch($event->user->id, $event->token)->onQueue('notifications');
    }
}
