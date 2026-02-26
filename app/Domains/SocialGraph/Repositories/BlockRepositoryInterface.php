<?php

namespace App\Domains\SocialGraph\Repositories;

use App\Domains\SocialGraph\Models\Block;

interface BlockRepositoryInterface
{
    public function isBlocking(string $blockerId, string $blockedId): bool;

    public function isBlockedBy(string $userId, string $blockerId): bool;

    public function eitherBlocked(string $userA, string $userB): bool;

    public function create(string $blockerId, string $blockedId): Block;

    public function delete(string $blockerId, string $blockedId): void;
}
