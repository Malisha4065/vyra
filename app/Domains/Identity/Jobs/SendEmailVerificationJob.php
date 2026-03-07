<?php

namespace App\Domains\Identity\Jobs;

use App\Domains\Identity\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendEmailVerificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $userId,
    ) {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        $user = User::query()->find($this->userId);

        if ($user === null || $user->hasVerifiedEmail()) {
            return;
        }

        $user->sendEmailVerificationNotification();
    }
}
