<?php

namespace App\Domains\Identity\Data;

use App\Domains\Identity\Models\User;
use Spatie\LaravelData\Data;

class UserData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $username,
        public readonly string $email,
        public readonly ?string $created_at,
    ) {}

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            username: $user->username,
            email: $user->email,
            created_at: $user->created_at?->toIso8601String(),
        );
    }
}
