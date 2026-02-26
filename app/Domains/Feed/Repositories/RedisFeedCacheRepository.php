<?php

namespace App\Domains\Feed\Repositories;

use App\Domains\Feed\ValueObjects\FeedKey;
use Illuminate\Support\Facades\Redis;

class RedisFeedCacheRepository implements FeedCacheRepositoryInterface
{
    private const FEED_MAX_ITEMS = 5000;

    public function addPostToUserFeed(string $userId, string $postId, int $score): void
    {
        $key = FeedKey::userFeed($userId);

        Redis::zadd($key, $score, $postId);
        Redis::zremrangebyrank($key, 0, -1 * (self::FEED_MAX_ITEMS + 1));
    }

    public function addPostToUserFeedIfAbsent(string $userId, string $postId, int $score, int $ttlSeconds = 604800): bool
    {
        $idempotencyKey = $this->idempotencyKey($userId, $postId);
        $acquired = Redis::set($idempotencyKey, '1', 'EX', $ttlSeconds, 'NX');

        if ($acquired !== true && $acquired !== 'OK') {
            return false;
        }

        $this->addPostToUserFeed($userId, $postId, $score);

        return true;
    }

    public function addPostToUserFeeds(array $userIds, string $postId, int $score): void
    {
        if ($userIds === []) {
            return;
        }

        Redis::pipeline(function ($pipeline) use ($userIds, $postId, $score): void {
            foreach ($userIds as $userId) {
                $key = FeedKey::userFeed($userId);
                $pipeline->zadd($key, $score, $postId);
                $pipeline->zremrangebyrank($key, 0, -1 * (self::FEED_MAX_ITEMS + 1));
            }
        });
    }

    public function addPostToUserFeedsIfAbsent(array $userIds, string $postId, int $score, int $ttlSeconds = 604800): void
    {
        foreach ($userIds as $userId) {
            $this->addPostToUserFeedIfAbsent($userId, $postId, $score, $ttlSeconds);
        }
    }

    public function getUserFeed(string $userId, int $limit = 50, ?int $beforeScore = null): array
    {
        $key = FeedKey::userFeed($userId);
        $max = $beforeScore === null ? '+inf' : '('.$beforeScore;

        $rows = Redis::zrevrangebyscore($key, $max, '-inf', [
            'withscores' => true,
            'limit' => [0, $limit],
        ]);

        $results = [];

        foreach ($rows as $postId => $score) {
            $results[] = [
                'post_id' => (string) $postId,
                'score' => (int) $score,
            ];
        }

        return $results;
    }

    public function removePostFromUserFeed(string $userId, string $postId): void
    {
        Redis::zrem(FeedKey::userFeed($userId), $postId);
    }

    public function removePostFromUserFeeds(array $userIds, string $postId): void
    {
        if ($userIds === []) {
            return;
        }

        Redis::pipeline(function ($pipeline) use ($userIds, $postId): void {
            foreach ($userIds as $userId) {
                $pipeline->zrem(FeedKey::userFeed($userId), $postId);
            }
        });
    }

    private function idempotencyKey(string $userId, string $postId): string
    {
        return "feed:idempotency:user:{$userId}:post:{$postId}";
    }
}
