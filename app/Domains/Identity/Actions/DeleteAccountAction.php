<?php

namespace App\Domains\Identity\Actions;

use App\Domains\Identity\Data\DeleteAccountData;
use App\Domains\Identity\Events\AccountDeleted;
use App\Domains\Identity\Models\User;
use App\Domains\Identity\Repositories\UserRepositoryInterface;

class DeleteAccountAction
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(User $user, DeleteAccountData $data): void
    {
        event(new AccountDeleted(
            userId: $user->id,
            username: $user->username,
            email: $user->email,
        ));

        $this->userRepository->delete($user);
    }
}
