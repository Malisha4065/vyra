<?php

namespace App\Domains\Feed\Actions;

use App\Domains\Feed\Data\FanOutPostOnWriteData;
use App\Domains\Feed\Repositories\FeedCacheRepositoryInterface;
use App\Domains\Feed\Repositories\HybridFeedRepositoryInterface;
use App\Domains\Feed\ValueObjects\FeedFanOutThreshold;
use App\Domains\SocialGraph\Repositories\FollowRepositoryInterface;

class FanOutPostOnWriteAction
{
    public function __construct(
        private readonly FollowRepositoryInterface $followRepository,
        private readonly FeedCacheRepositoryInterface $feedCacheRepository,
        private readonly HybridFeedRepositoryInterface $hybridFeedRepository,
    ) {}

    /**
     * @return 'fanout'|'hybrid'
     */
    public function __invoke(FanOutPostOnWriteData $data): string
    {
        $this->feedCacheRepository->addPostToUserFeed($data->author_id, $data->post_id, $data->published_at);

        $followersCount = $this->followRepository->followersCount($data->author_id);

        if ($followersCount >= FeedFanOutThreshold::HYBRID_THRESHOLD) {
            $this->hybridFeedRepository->markAuthorAsHighFollower($data->author_id);
            $this->hybridFeedRepository->addPostForHighFollowerAuthor(
                authorId: $data->author_id,
                postId: $data->post_id,
                score: $data->published_at,
            );

            return 'hybrid';
        }

        $this->hybridFeedRepository->unmarkAuthorAsHighFollower($data->author_id);

        $followerIds = $this->followRepository->getFollowerIds($data->author_id);

        $this->feedCacheRepository->addPostToUserFeeds($followerIds, $data->post_id, $data->published_at);

        return 'fanout';
    }
}
