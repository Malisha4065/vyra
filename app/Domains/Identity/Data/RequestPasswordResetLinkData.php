<?php

namespace App\Domains\Identity\Data;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;

class RequestPasswordResetLinkData extends Data
{
    public function __construct(
        #[Email, Max(255)]
        public readonly string $email,
    ) {}
}
