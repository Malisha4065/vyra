<?php

namespace App\Domains\Identity\Listeners;

use App\Domains\Identity\Events\EmailVerificationRequested;
use App\Domains\Identity\Events\UserRegistered;
use App\Domains\Identity\Jobs\SendEmailVerificationJob;

class QueueSendEmailVerificationListener
{
    public function handle(UserRegistered|EmailVerificationRequested $event): void
    {
        SendEmailVerificationJob::dispatch($event->user->id)->onQueue('notifications');
    }
}
