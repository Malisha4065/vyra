<?php

namespace App\Domains\Content\Repositories;

use App\Domains\Content\Models\PostReaction;

interface PostReactionRepositoryInterface
{
    public function upsert(string $postId, string $userId, string $type): PostReaction;

    public function findByUserAndPost(string $postId, string $userId): ?PostReaction;

    public function deleteByUserAndPost(string $postId, string $userId): ?PostReaction;

    /**
     * @return array{total: int, by_type: array<string, int>}
     */
    public function aggregateForPost(string $postId): array;
}
