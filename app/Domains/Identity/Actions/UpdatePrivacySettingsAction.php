<?php

namespace App\Domains\Identity\Actions;

use App\Domains\Identity\Data\UpdatePrivacySettingsData;
use App\Domains\Identity\Models\User;
use App\Domains\Identity\Models\UserProfile;
use App\Domains\Identity\Repositories\UserProfileRepositoryInterface;

class UpdatePrivacySettingsAction
{
    public function __construct(
        private readonly UserProfileRepositoryInterface $profileRepository,
    ) {}

    public function __invoke(User $user, UpdatePrivacySettingsData $data): UserProfile
    {
        $profile = $this->profileRepository->findByUserId($user->id);

        return $this->profileRepository->update($profile, [
            'is_private' => $data->is_private,
        ]);
    }
}
