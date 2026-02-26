<?php

namespace App\Domains\Identity\Repositories;

use App\Domains\Identity\Models\UserProfile;

interface UserProfileRepositoryInterface
{
    public function findByUserId(string $userId): ?UserProfile;

    public function create(array $attributes): UserProfile;

    public function update(UserProfile $profile, array $attributes): UserProfile;
}
