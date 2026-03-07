<?php

namespace App\Domains\Identity\Actions;

use App\Domains\Identity\Events\EmailVerificationRequested;
use App\Domains\Identity\Models\User;

class RequestEmailVerificationAction
{
    public function __invoke(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
            return;
        }

        event(new EmailVerificationRequested($user));
    }
}
