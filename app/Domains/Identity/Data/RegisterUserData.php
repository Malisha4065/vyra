<?php

namespace App\Domains\Identity\Data;

use Spatie\LaravelData\Attributes\Validation\Confirmed;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Regex;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Data;

class RegisterUserData extends Data
{
    public function __construct(
        #[Min(3), Max(30), Regex('/^[a-zA-Z0-9_]+$/'), Unique('users', 'username')]
        public readonly string $username,

        #[Max(255), Unique('users', 'email')]
        public readonly string $email,

        #[Min(8), Max(255), Confirmed]
        public readonly string $password,

        public readonly string $password_confirmation,
    ) {}
}
