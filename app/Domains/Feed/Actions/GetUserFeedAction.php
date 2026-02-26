<?php

namespace App\Domains\Feed\Actions;

use App\Domains\Content\Models\Post;
use App\Domains\Content\Repositories\PostRepositoryInterface;
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
        private readonly PostRepositoryInterface $postRepository,
    ) {}

    /**
     * @return array{items: array<int, array{post_id: string, score: int, post: array<string, mixed>}>, next_cursor: int|null}
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

        foreach ($rankedItems as $item) {
            $post = $postsById[$item['post_id']] ?? null;

            if ($post === null) {
                continue;
            }

            $items[] = [
                'post_id' => $item['post_id'],
                'score' => $item['score'],
                'post' => $this->serializePost($post),
            ];
        }

        $nextCursor = $items === [] ? null : (int) end($items)['score'];

        return [
            'items' => $items,
            'next_cursor' => $nextCursor,
        ];
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
