<?php

namespace App\Domains\Feed\Jobs;

use App\Domains\Feed\Repositories\FeedCacheRepositoryInterface;
use App\Domains\SocialGraph\Repositories\FollowRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PushPostToFollowersChunkJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $postId,
        public readonly string $authorId,
        public readonly int $score,
        public readonly ?string $afterFollowerId = null,
        public readonly int $chunkSize = 1000,
    ) {}

    public function handle(
        FollowRepositoryInterface $followRepository,
        FeedCacheRepositoryInterface $feedCacheRepository,
    ): void {
        $followerIds = $followRepository->getFollowerIdsChunk(
            userId: $this->authorId,
            afterFollowerId: $this->afterFollowerId,
            limit: $this->chunkSize,
        );

        if ($followerIds === []) {
            return;
        }

        $feedCacheRepository->addPostToUserFeedsIfAbsent(
            userIds: $followerIds,
            postId: $this->postId,
            score: $this->score,
        );

        if (count($followerIds) < $this->chunkSize) {
            return;
        }

        $nextAfter = end($followerIds);

        self::dispatch(
            postId: $this->postId,
            authorId: $this->authorId,
            score: $this->score,
            afterFollowerId: $nextAfter === false ? null : (string) $nextAfter,
            chunkSize: $this->chunkSize,
        )->onQueue('feed');
    }
}
