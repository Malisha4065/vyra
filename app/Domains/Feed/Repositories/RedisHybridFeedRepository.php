<?php

namespace App\Domains\Feed\Repositories;

use App\Domains\Feed\ValueObjects\FeedKey;
use Illuminate\Support\Facades\Redis;

class RedisHybridFeedRepository implements HybridFeedRepositoryInterface
{
    private const AUTHOR_POSTS_MAX_ITEMS = 500;

    public function markAuthorAsHighFollower(string $authorId): void
    {
        Redis::sadd(FeedKey::highFollowerAuthorsSet(), $authorId);
    }

    public function unmarkAuthorAsHighFollower(string $authorId): void
    {
        Redis::srem(FeedKey::highFollowerAuthorsSet(), $authorId);
    }

    public function addPostForHighFollowerAuthor(string $authorId, string $postId, int $score): void
    {
        $key = FeedKey::highFollowerAuthorPosts($authorId);

        Redis::zadd($key, $score, $postId);
        Redis::zremrangebyrank($key, 0, -1 * (self::AUTHOR_POSTS_MAX_ITEMS + 1));
    }

    public function removePostForHighFollowerAuthor(string $authorId, string $postId): void
    {
        Redis::zrem(FeedKey::highFollowerAuthorPosts($authorId), $postId);
    }

    public function filterHighFollowerAuthors(array $authorIds): array
    {
        if ($authorIds === []) {
            return [];
        }

        $highAuthors = [];

        foreach ($authorIds as $authorId) {
            if ((int) Redis::sismember(FeedKey::highFollowerAuthorsSet(), $authorId) === 1) {
                $highAuthors[] = $authorId;
            }
        }

        return $highAuthors;
    }

    public function getRecentPostsForHighFollowerAuthors(array $authorIds, int $limitPerAuthor = 5, ?int $beforeScore = null): array
    {
        if ($authorIds === []) {
            return [];
        }

        $max = $beforeScore === null ? '+inf' : '('.$beforeScore;
        $results = [];

        foreach ($authorIds as $authorId) {
            $rows = Redis::zrevrangebyscore(FeedKey::highFollowerAuthorPosts($authorId), $max, '-inf', [
                'withscores' => true,
                'limit' => [0, $limitPerAuthor],
            ]);

            foreach ($rows as $postId => $score) {
                $results[] = [
                    'post_id' => (string) $postId,
                    'score' => (int) $score,
                    'author_id' => $authorId,
                ];
            }
        }

        return $results;
    }
}
