<?php

namespace App\Domains\Feed\Actions;

use App\Domains\Feed\Data\RemovePostFromFeedsData;
use App\Domains\Feed\Repositories\FeedCacheRepositoryInterface;
use App\Domains\Feed\Repositories\HybridFeedRepositoryInterface;
use App\Domains\Feed\ValueObjects\FeedFanOutThreshold;
use App\Domains\SocialGraph\Repositories\FollowRepositoryInterface;

class RemovePostFromFeedsAction
{
    public function __construct(
        private readonly FollowRepositoryInterface $followRepository,
        private readonly FeedCacheRepositoryInterface $feedCacheRepository,
        private readonly HybridFeedRepositoryInterface $hybridFeedRepository,
    ) {}

    public function __invoke(RemovePostFromFeedsData $data): void
    {
        $this->feedCacheRepository->removePostFromUserFeed($data->author_id, $data->post_id);

        $followersCount = $this->followRepository->followersCount($data->author_id);

        if ($followersCount >= FeedFanOutThreshold::HYBRID_THRESHOLD) {
            $this->hybridFeedRepository->removePostForHighFollowerAuthor($data->author_id, $data->post_id);

            return;
        }

        $followerIds = $this->followRepository->getFollowerIds($data->author_id);
        $this->feedCacheRepository->removePostFromUserFeeds($followerIds, $data->post_id);
    }
}
