<?php

namespace App\Domains\Identity\Jobs;

use App\Domains\Identity\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendPasswordResetLinkJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $userId,
        public readonly string $token,
    ) {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        $user = User::query()->find($this->userId);

        if ($user === null) {
            return;
        }

        $user->notify(new ResetPassword($this->token));
    }
}
