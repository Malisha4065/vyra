<?php

namespace App\Domains\Identity\Actions;

use App\Domains\Identity\Data\UpdatePasswordData;
use App\Domains\Identity\Events\PasswordUpdated;
use App\Domains\Identity\Models\User;
use App\Domains\Identity\Repositories\UserRepositoryInterface;

class UpdatePasswordAction
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(User $user, UpdatePasswordData $data): User
    {
        $updatedUser = $this->userRepository->update($user, [
            'password' => $data->password,
        ]);

        event(new PasswordUpdated($updatedUser));

        return $updatedUser;
    }
}
