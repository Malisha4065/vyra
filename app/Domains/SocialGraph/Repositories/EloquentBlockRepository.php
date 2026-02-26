<?php

namespace App\Domains\SocialGraph\Repositories;

use App\Domains\SocialGraph\Models\Block;

class EloquentBlockRepository implements BlockRepositoryInterface
{
    public function __construct(
        private readonly Block $model,
    ) {}

    public function isBlocking(string $blockerId, string $blockedId): bool
    {
        return $this->model
            ->where('blocker_id', $blockerId)
            ->where('blocked_id', $blockedId)
            ->exists();
    }

    public function isBlockedBy(string $userId, string $blockerId): bool
    {
        return $this->isBlocking($blockerId, $userId);
    }

    public function eitherBlocked(string $userA, string $userB): bool
    {
        return $this->model
            ->where(function ($query) use ($userA, $userB) {
                $query->where('blocker_id', $userA)->where('blocked_id', $userB);
            })
            ->orWhere(function ($query) use ($userA, $userB) {
                $query->where('blocker_id', $userB)->where('blocked_id', $userA);
            })
            ->exists();
    }

    public function create(string $blockerId, string $blockedId): Block
    {
        return $this->model->create([
            'blocker_id' => $blockerId,
            'blocked_id' => $blockedId,
        ]);
    }

    public function delete(string $blockerId, string $blockedId): void
    {
        $this->model
            ->where('blocker_id', $blockerId)
            ->where('blocked_id', $blockedId)
            ->delete();
    }
}
