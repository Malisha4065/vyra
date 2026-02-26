<?php

namespace App\Domains\SocialGraph\Policies;

use App\Domains\Identity\Models\User;
use App\Domains\SocialGraph\Repositories\BlockRepositoryInterface;

class FollowPolicy
{
    public function __construct(
        private readonly BlockRepositoryInterface $blockRepository,
    ) {}

    /**
     * Determine if the user can follow the target.
     */
    public function follow(User $authUser, User $target): bool
    {
        // Cannot follow yourself
        if ($authUser->id === $target->id) {
            return false;
        }

        // Cannot follow if either user has blocked the other
        return ! $this->blockRepository->eitherBlocked($authUser->id, $target->id);
    }

    /**
     * Determine if the user can unfollow the target.
     */
    public function unfollow(User $authUser, User $target): bool
    {
        return $authUser->id !== $target->id;
    }

    /**
     * Determine if the user can block the target.
     */
    public function block(User $authUser, User $target): bool
    {
        return $authUser->id !== $target->id;
    }

    /**
     * Determine if the user can unblock the target.
     */
    public function unblock(User $authUser, User $target): bool
    {
        return $authUser->id !== $target->id;
    }

    /**
     * Determine if the user can mute the target.
     */
    public function mute(User $authUser, User $target): bool
    {
        return $authUser->id !== $target->id;
    }

    /**
     * Determine if the user can unmute the target.
     */
    public function unmute(User $authUser, User $target): bool
    {
        return $authUser->id !== $target->id;
    }
}
