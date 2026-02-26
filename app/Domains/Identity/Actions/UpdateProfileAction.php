<?php

namespace App\Domains\Identity\Actions;

use App\Domains\Identity\Data\UpdateProfileData;
use App\Domains\Identity\Events\ProfileUpdated;
use App\Domains\Identity\Models\User;
use App\Domains\Identity\Models\UserProfile;
use App\Domains\Identity\Repositories\UserProfileRepositoryInterface;

class UpdateProfileAction
{
    public function __construct(
        private readonly UserProfileRepositoryInterface $profileRepository,
    ) {}

    public function __invoke(User $user, UpdateProfileData $data): UserProfile
    {
        $profile = $this->profileRepository->findByUserId($user->id);

        $attributes = array_filter($data->toArray(), fn ($value) => $value !== null);

        $profile = $this->profileRepository->update($profile, $attributes);

        event(new ProfileUpdated($profile));

        return $profile;
    }
}
