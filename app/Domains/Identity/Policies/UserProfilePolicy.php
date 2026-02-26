<?php

namespace App\Domains\Identity\Policies;

use App\Domains\Identity\Models\User;
use App\Domains\Identity\Models\UserProfile;

class UserProfilePolicy
{
    /**
     * Determine if the given profile can be viewed by the user.
     */
    public function view(User $authUser, UserProfile $profile): bool
    {
        // Owner can always view their own profile
        if ($authUser->id === $profile->user_id) {
            return true;
        }

        // Public profiles are viewable by any authenticated user
        if (! $profile->is_private) {
            return true;
        }

        // Private profiles: only approved followers can view
        // (Will be enhanced when SocialGraph domain is built)
        return false;
    }

    /**
     * Determine if the user can update the profile.
     */
    public function update(User $authUser, UserProfile $profile): bool
    {
        return $authUser->id === $profile->user_id;
    }
}
