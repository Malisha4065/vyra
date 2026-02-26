<?php

namespace App\Domains\Feed\Actions;

use App\Domains\Feed\Data\GetUserFeedData;
use App\Domains\Feed\Repositories\FeedCacheRepositoryInterface;
use App\Domains\Feed\Repositories\HybridFeedRepositoryInterface;
use App\Domains\SocialGraph\Repositories\FollowRepositoryInterface;

class GetUserFeedAction
{
    public function __construct(
        private readonly FeedCacheRepositoryInterface $feedCacheRepository,
        private readonly HybridFeedRepositoryInterface $hybridFeedRepository,
        private readonly FollowRepositoryInterface $followRepository,
    ) {}

    /**
     * @return array{items: array<int, array{post_id: string, score: int}>, next_cursor: int|null}
     */
    public function __invoke(GetUserFeedData $data): array
    {
        $cachedFeed = $this->feedCacheRepository->getUserFeed(
            userId: $data->user_id,
            limit: $data->limit,
            beforeScore: $data->before_score,
        );

        $followingIds = $this->followRepository->getFollowingIds($data->user_id);
        $highFollowerAuthors = $this->hybridFeedRepository->filterHighFollowerAuthors($followingIds);

        $hybridFeed = $this->hybridFeedRepository->getRecentPostsForHighFollowerAuthors(
            authorIds: $highFollowerAuthors,
            limitPerAuthor: $data->hybrid_per_author,
            beforeScore: $data->before_score,
        );

        $merged = [];

        foreach ($cachedFeed as $item) {
            $merged[$item['post_id']] = $item['score'];
        }

        foreach ($hybridFeed as $item) {
            $existing = $merged[$item['post_id']] ?? null;

            if ($existing === null || $item['score'] > $existing) {
                $merged[$item['post_id']] = $item['score'];
            }
        }

        arsort($merged);

        $items = [];

        foreach ($merged as $postId => $score) {
            $items[] = [
                'post_id' => (string) $postId,
                'score' => (int) $score,
            ];

            if (count($items) >= $data->limit) {
                break;
            }
        }

        $nextCursor = $items === [] ? null : (int) end($items)['score'];

        return [
            'items' => $items,
            'next_cursor' => $nextCursor,
        ];
    }
}
