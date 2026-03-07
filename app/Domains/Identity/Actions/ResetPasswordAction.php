<?php

namespace App\Domains\Identity\Actions;

use App\Domains\Identity\Data\ResetPasswordData;
use App\Domains\Identity\Events\PasswordUpdated;
use App\Domains\Identity\Exceptions\InvalidPasswordResetTokenException;
use App\Domains\Identity\Models\User;
use App\Domains\Identity\Repositories\UserRepositoryInterface;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordAction
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * @throws InvalidPasswordResetTokenException
     */
    public function __invoke(ResetPasswordData $data): void
    {
        $status = Password::broker()->reset([
            'email' => strtolower($data->email),
            'password' => $data->password,
            'password_confirmation' => $data->password_confirmation,
            'token' => $data->token,
        ], function (User $user) use ($data): void {
            $updatedUser = $this->userRepository->update($user, [
                'password' => $data->password,
                'remember_token' => Str::random(60),
            ]);

            event(new PasswordUpdated($updatedUser));
            event(new PasswordReset($updatedUser));
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw new InvalidPasswordResetTokenException();
        }
    }
}
