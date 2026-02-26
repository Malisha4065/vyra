<?php

namespace App\Domains\Identity\Actions;

use App\Domains\Identity\Data\RegisterUserData;
use App\Domains\Identity\Events\UserRegistered;
use App\Domains\Identity\Models\User;
use App\Domains\Identity\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class RegisterUserAction
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(RegisterUserData $data): User
    {
        $user = $this->userRepository->create([
            'username' => strtolower($data->username),
            'email' => strtolower($data->email),
            'password' => Hash::make($data->password),
        ]);

        event(new UserRegistered($user));

        return $user;
    }
}
