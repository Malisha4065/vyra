<?php

namespace App\Domains\Notification\Policies;

use App\Domains\Identity\Models\User;

class UserNotificationPreferencePolicy
{
    public function view(User $authUser): bool
    {
        return $authUser->id !== '';
    }

    public function update(User $authUser): bool
    {
        return $authUser->id !== '';
    }
}
