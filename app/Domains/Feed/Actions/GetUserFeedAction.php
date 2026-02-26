<?php

namespace App\Domains\Feed\Actions;

use App\Domains\Content\Models\Post;
use App\Domains\Content\Repositories\PostRepositoryInterface;
use App\Domains\Feed\Data\FeedAuthorData;
use App\Domains\Feed\Data\FeedItemData;
use App\Domains\Feed\Data\FeedPostData;
use App\Domains\Feed\Data\GetUserFeedData;
use App\Domains\Feed\Data\UserFeedResponseData;
use App\Domains\Feed\Repositories\FeedCacheRepositoryInterface;
use App\Domains\Feed\Repositories\HybridFeedRepositoryInterface;
use App\Domains\SocialGraph\Repositories\BlockRepositoryInterface;
use App\Domains\SocialGraph\Repositories\FollowRepositoryInterface;
use App\Domains\SocialGraph\Repositories\MuteRepositoryInterface;

class GetUserFeedAction
{
    public function __construct(
        private readonly FeedCacheRepositoryInterface $feedCacheRepository,
        private readonly HybridFeedRepositoryInterface $hybridFeedRepository,
        private readonly FollowRepositoryInterface $followRepository,
        private readonly PostRepositoryInterface $postRepository,
        private readonly BlockRepositoryInterface $blockRepository,
        private readonly MuteRepositoryInterface $muteRepository,
    ) {}

    public function __invoke(GetUserFeedData $data): UserFeedResponseData
    {
        $fetchLimit = min($data->limit * 3, 200);

        $cachedFeed = $this->feedCacheRepository->getUserFeed(
            userId: $data->user_id,
            limit: $fetchLimit,
            beforeScore: $data->before_score,
        );

        $followingIds = $this->followRepository->getFollowingIds($data->user_id);
        $highFollowerAuthors = $this->hybridFeedRepository->filterHighFollowerAuthors($followingIds);

        $hybridFeed = $this->hybridFeedRepository->getRecentPostsForHighFollowerAuthors(
            authorIds: $highFollowerAuthors,
            limitPerAuthor: min($data->hybrid_per_author * 2, 20),
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

        $rankedItems = [];

        foreach ($merged as $postId => $score) {
            $rankedItems[] = [
                'post_id' => (string) $postId,
                'score' => (int) $score,
            ];

            if (count($rankedItems) >= $data->limit) {
                break;
            }
        }

        $postsById = $this->hydratePosts($rankedItems);
        $items = [];
        $visibilityMap = [];

        foreach ($rankedItems as $item) {
            $post = $postsById[$item['post_id']] ?? null;

            if ($post === null) {
                continue;
            }

            if (! $this->isAuthorVisible($data->user_id, $post->user_id, $visibilityMap)) {
                continue;
            }

            $items[] = [
                'post_id' => $item['post_id'],
                'score' => $item['score'],
                'post' => $this->serializePost($post),
            ];
        }

        $nextCursor = null;

        if (count($items) === $data->limit) {
            $nextCursor = (int) end($items)['score'];
        } elseif ($rankedItems !== []) {
            // Cursor hardening: progress pagination even when ranked entries contain stale/deleted posts.
            $nextCursor = (int) end($rankedItems)['score'];
        }

        $feedItems = array_map(function (array $item): FeedItemData {
            /** @var array<string, mixed> $post */
            $post = $item['post'];

            return new FeedItemData(
                post_id: $item['post_id'],
                score: $item['score'],
                post: new FeedPostData(
                    id: $post['id'],
                    user_id: $post['user_id'],
                    body: $post['body'],
                    published_at: $post['published_at'],
                    author: new FeedAuthorData(
                        id: $post['author']['id'],
                        username: $post['author']['username'],
                    ),
                    counts: $post['counts'],
                ),
            );
        }, $items);

        return new UserFeedResponseData(
            items: $feedItems,
            next_cursor: $nextCursor,
        );
    }

    /**
     * @param array<string, bool> $visibilityMap
     */
    private function isAuthorVisible(string $viewerId, string $authorId, array &$visibilityMap): bool
    {
        if ($viewerId === $authorId) {
            return true;
        }

        if (array_key_exists($authorId, $visibilityMap)) {
            return $visibilityMap[$authorId];
        }

        if ($this->muteRepository->isMuting($viewerId, $authorId)) {
            return $visibilityMap[$authorId] = false;
        }

        if ($this->blockRepository->eitherBlocked($viewerId, $authorId)) {
            return $visibilityMap[$authorId] = false;
        }

        return $visibilityMap[$authorId] = true;
    }

    /**
     * @param array<int, array{post_id: string, score: int}> $rankedItems
     * @return array<string, Post>
     */
    private function hydratePosts(array $rankedItems): array
    {
        $ids = array_map(static fn (array $item): string => $item['post_id'], $rankedItems);
        $posts = $this->postRepository->findByIds($ids);
        $byId = [];

        foreach ($posts as $post) {
            $byId[$post->id] = $post;
        }

        return $byId;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializePost(Post $post): array
    {
        $commentsCount = $post->relationLoaded('comments') ? $post->comments->count() : 0;
        $reactionsCount = $post->relationLoaded('reactions') ? $post->reactions->count() : 0;
        $author = $post->relationLoaded('author') ? $post->author : null;

        return [
            'id' => $post->id,
            'user_id' => $post->user_id,
            'body' => $post->body,
            'published_at' => $post->published_at?->toIso8601String(),
            'author' => [
                'id' => $author?->id,
                'username' => $author?->username,
            ],
            'counts' => [
                'comments' => $commentsCount,
                'reactions' => $reactionsCount,
            ],
        ];
    }
}
