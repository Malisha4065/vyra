<?php

namespace App\Domains\Identity\Actions;

use App\Domains\Identity\Models\User;

class BuildUserSearchPayloadAction
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(User $user): array
    {
        $profile = $user->relationLoaded('profile') ? $user->profile : null;

        return [
            'id' => $user->id,
            'username' => $user->username,
            'display_name' => $profile?->display_name,
            'bio' => $profile?->bio,
            'avatar_url' => $profile?->avatar_url,
            'is_private' => (bool) ($profile?->is_private ?? false),
        ];
    }
}
