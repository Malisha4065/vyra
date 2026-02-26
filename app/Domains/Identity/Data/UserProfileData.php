<?php

namespace App\Domains\Identity\Data;

use App\Domains\Identity\Models\UserProfile;
use Spatie\LaravelData\Data;

class UserProfileData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $user_id,
        public readonly ?string $display_name,
        public readonly ?string $bio,
        public readonly ?string $avatar_url,
        public readonly ?string $cover_url,
        public readonly ?string $website,
        public readonly ?string $location,
        public readonly ?string $date_of_birth,
        public readonly bool $is_private,
    ) {}

    public static function fromModel(UserProfile $profile): self
    {
        return new self(
            id: $profile->id,
            user_id: $profile->user_id,
            display_name: $profile->display_name,
            bio: $profile->bio,
            avatar_url: $profile->avatar_url,
            cover_url: $profile->cover_url,
            website: $profile->website,
            location: $profile->location,
            date_of_birth: $profile->date_of_birth?->format('Y-m-d'),
            is_private: $profile->is_private,
        );
    }
}
