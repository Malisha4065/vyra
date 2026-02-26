<?php

namespace App\Domains\Identity\Repositories;

use App\Domains\Identity\Models\UserProfile;

class EloquentUserProfileRepository implements UserProfileRepositoryInterface
{
    public function __construct(
        private readonly UserProfile $model,
    ) {}

    public function findByUserId(string $userId): ?UserProfile
    {
        return $this->model->where('user_id', $userId)->first();
    }

    public function create(array $attributes): UserProfile
    {
        return $this->model->create($attributes);
    }

    public function update(UserProfile $profile, array $attributes): UserProfile
    {
        $profile->update($attributes);

        return $profile->fresh();
    }
}
