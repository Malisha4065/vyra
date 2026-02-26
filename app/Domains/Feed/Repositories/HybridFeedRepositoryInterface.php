<?php

namespace App\Domains\Feed\Repositories;

interface HybridFeedRepositoryInterface
{
    public function markAuthorAsHighFollower(string $authorId): void;

    public function unmarkAuthorAsHighFollower(string $authorId): void;

    public function addPostForHighFollowerAuthor(string $authorId, string $postId, int $score): void;

    public function removePostForHighFollowerAuthor(string $authorId, string $postId): void;

    /**
     * @param array<int, string> $authorIds
     * @return array<int, string>
     */
    public function filterHighFollowerAuthors(array $authorIds): array;

    /**
     * @param array<int, string> $authorIds
     * @return array<int, array{post_id: string, score: int, author_id: string}>
     */
    public function getRecentPostsForHighFollowerAuthors(array $authorIds, int $limitPerAuthor = 5, ?int $beforeScore = null): array;
}
