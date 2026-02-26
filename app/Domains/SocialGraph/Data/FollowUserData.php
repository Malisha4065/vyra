<?php

namespace App\Domains\SocialGraph\Data;

use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Data;

class FollowUserData extends Data
{
    public function __construct(
        #[Exists('users', 'id')]
        public readonly string $target_user_id,
    ) {}
}
