<?php

namespace App\Domains\Identity\Actions;

use App\Domains\Identity\Exceptions\InvalidEmailVerificationLinkException;
use App\Domains\Identity\Models\User;
use Illuminate\Auth\Events\Verified;

class VerifyEmailAction
{
    /**
     * @throws InvalidEmailVerificationLinkException
     */
    public function __invoke(User $user, string $hash): bool
    {
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            throw new InvalidEmailVerificationLinkException();
        }

        if ($user->hasVerifiedEmail()) {
            return false;
        }

        $user->markEmailAsVerified();

        event(new Verified($user));

        return true;
    }
}
