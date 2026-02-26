<?php

namespace App\Domains\Communication\ValueObjects;

use App\Domains\Identity\Models\User;

class DirectConversationKey
{
    public function __construct(
        public readonly string $value,
    ) {}

    public static function fromUserIds(string $userA, string $userB): self
    {
        $ids = [$userA, $userB];
        sort($ids);

        return new self(implode(':', $ids));
    }

    public static function fromUsers(User $userA, User $userB): self
    {
        return self::fromUserIds($userA->id, $userB->id);
    }
}
