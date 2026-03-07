<?php

namespace App\Domains\Feed\Actions;

use App\Domains\Content\Repositories\PostRepositoryInterface;
use App\Domains\Feed\Data\RebuildUserFeedCacheData;
use App\Domains\Feed\Repositories\FeedCacheRepositoryInterface;
use App\Domains\Feed\Repositories\HybridFeedRepositoryInterface;
use App\Domains\SocialGraph\Repositories\FollowRepositoryInterface;

class RebuildUserFeedCacheAction
{
    public function __construct(
        private readonly FollowRepositoryInterface $followRepository,
        private readonly FeedCacheRepositoryInterface $feedCacheRepository,
        private readonly HybridFeedRepositoryInterface $hybridFeedRepository,
        private readonly PostRepositoryInterface $postRepository,
    ) {}

    public function __invoke(RebuildUserFeedCacheData $data): int
    {
        $followingIds = $this->followRepository->getFollowingIds($data->user_id);
        $highFollowerAuthors = $this->hybridFeedRepository->filterHighFollowerAuthors($followingIds);
        $regularAuthors = array_values(array_unique(array_merge(
            [$data->user_id],
            array_values(array_diff($followingIds, $highFollowerAuthors)),
        )));

        $this->feedCacheRepository->clearUserFeed($data->user_id);

        $posts = $this->postRepository->getRecentPublishedPostIdsByAuthors($regularAuthors, $data->limit);

        foreach ($posts as $post) {
            $this->feedCacheRepository->addPostToUserFeed(
                userId: $data->user_id,
                postId: $post['post_id'],
                score: $post['score'],
            );
        }

        return count($posts);
    }
}
