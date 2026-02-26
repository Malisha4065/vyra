<?php

namespace App\Domains\Notification\Policies;

use App\Domains\Identity\Models\User;
use App\Domains\Notification\Models\UserNotification;

class UserNotificationPolicy
{
    public function viewAny(User $authUser): bool
    {
        return $authUser->id !== '';
    }

    public function markRead(User $authUser, UserNotification $notification): bool
    {
        return $authUser->id === $notification->user_id;
    }

    public function markAllRead(User $authUser): bool
    {
        return $authUser->id !== '';
    }
}
