<?php

namespace App\Domains\Identity\Policies;

use App\Domains\Identity\Models\User;
use App\Domains\Identity\Models\UserProfile;
use App\Domains\SocialGraph\Repositories\BlockRepositoryInterface;
use App\Domains\SocialGraph\Repositories\FollowRepositoryInterface;

class UserProfilePolicy
{
    public function __construct(
        private readonly FollowRepositoryInterface $followRepository,
        private readonly BlockRepositoryInterface $blockRepository,
    ) {}

    /**
     * Determine if the given profile can be viewed by the user.
     */
    public function view(User $authUser, UserProfile $profile): bool
    {
        // Owner can always view their own profile
        if ($authUser->id === $profile->user_id) {
            return true;
        }

        if ($this->blockRepository->eitherBlocked($authUser->id, $profile->user_id)) {
            return false;
        }

        // Public profiles are viewable by any authenticated user
        if (! $profile->is_private) {
            return true;
        }

        // Private profiles: only approved followers can view
        return $this->followRepository->isFollowing($authUser->id, $profile->user_id);
    }

    /**
     * Determine if the user can update the profile.
     */
    public function update(User $authUser, UserProfile $profile): bool
    {
        return $authUser->id === $profile->user_id;
    }
}
