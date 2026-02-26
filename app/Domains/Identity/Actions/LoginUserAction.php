<?php

namespace App\Domains\Identity\Actions;

use App\Domains\Identity\Data\LoginData;
use App\Domains\Identity\Exceptions\InvalidCredentialsException;
use App\Domains\Identity\Models\User;
use Illuminate\Support\Facades\Auth;

class LoginUserAction
{
    /**
     * @throws InvalidCredentialsException
     */
    public function __invoke(LoginData $data): User
    {
        $credentials = [
            'email' => strtolower($data->email),
            'password' => $data->password,
        ];

        if (! Auth::attempt($credentials, $data->remember)) {
            throw new InvalidCredentialsException();
        }

        session()->regenerate();

        /** @var User */
        return Auth::user();
    }
}
