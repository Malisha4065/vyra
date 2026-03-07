<?php

namespace App\Domains\Identity\Actions;

use App\Domains\Identity\Data\RequestPasswordResetLinkData;
use App\Domains\Identity\Events\PasswordResetLinkRequested;
use App\Domains\Identity\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Password;

class RequestPasswordResetLinkAction
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(RequestPasswordResetLinkData $data): void
    {
        $user = $this->userRepository->findByEmail($data->email);

        if ($user === null) {
            return;
        }

        $token = Password::broker()->createToken($user);

        event(new PasswordResetLinkRequested($user, $token));
    }
}
