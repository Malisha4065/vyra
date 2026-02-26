<?php

namespace App\Domains\Feed\Policies;

use App\Domains\Identity\Models\User;

class FeedPolicy
{
    public function view(User $authUser, string $targetUserId): bool
    {
        return $authUser->id === $targetUserId;
    }
}
