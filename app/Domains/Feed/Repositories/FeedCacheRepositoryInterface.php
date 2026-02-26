<?php

namespace App\Domains\Feed\Repositories;

interface FeedCacheRepositoryInterface
{
    public function addPostToUserFeed(string $userId, string $postId, int $score): void;

    /**
     * @param array<int, string> $userIds
     */
    public function addPostToUserFeeds(array $userIds, string $postId, int $score): void;

    /**
     * @return array<int, array{post_id: string, score: int}>
     */
    public function getUserFeed(string $userId, int $limit = 50, ?int $beforeScore = null): array;

    public function removePostFromUserFeed(string $userId, string $postId): void;

    /**
     * @param array<int, string> $userIds
     */
    public function removePostFromUserFeeds(array $userIds, string $postId): void;
}
