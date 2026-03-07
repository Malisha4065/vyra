<?php

namespace App\Domains\Content\Actions;

use App\Domains\Content\Data\SearchContentData;
use App\Domains\Content\Models\Post;
use App\Domains\Content\Repositories\PostRepositoryInterface;
use App\Domains\Identity\Actions\BuildUserSearchPayloadAction;
use App\Domains\Identity\Repositories\UserRepositoryInterface;
use App\Domains\SocialGraph\Repositories\BlockRepositoryInterface;

class DiscoverContentAction
{
    public function __construct(
        private readonly PostRepositoryInterface $postRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly BlockRepositoryInterface $blockRepository,
        private readonly BuildUserSearchPayloadAction $buildUserPayload,
    ) {}

    /**
     * @return array{results: array<int, array<string, mixed>>, users: array<int, array<string, mixed>>, trending_hashtags: array<int, array{tag: string, count: int}>}
     */
    public function __invoke(SearchContentData $data): array
    {
        $query = trim((string) ($data->query ?? ''));
        $hashtag = $this->normalizeHashtag($data->hashtag);

        if ($query !== '' && str_starts_with($query, '#')) {
            $hashtag = $this->normalizeHashtag($query);
            $query = '';
        }

        $posts = $hashtag !== null
            ? $this->postRepository->findPublishedByHashtag($hashtag, $data->limit)
            : ($query !== ''
                ? $this->postRepository->searchPublished($query, $data->limit)
                : []);

        $users = $query !== ''
            ? array_values(array_filter(
                array_map(
                    fn ($user): ?array => $this->serializeUser($user, $data->user_id),
                    $this->userRepository->searchDiscoverable($query, min($data->limit, 10)),
                ),
                static fn ($user): bool => $user !== null,
            ))
            : [];

        return [
            'results' => array_map(fn (Post $post): array => $this->serializePost($post), $posts),
            'users' => $users,
            'trending_hashtags' => $this->postRepository->getTrendingHashtags(8),
        ];
    }

    private function normalizeHashtag(?string $value): ?string
    {
        $normalized = ltrim(trim((string) $value), '#');

        return $normalized === '' ? null : mb_strtolower($normalized);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializePost(Post $post): array
    {
        return [
            'id' => $post->id,
            'body' => $post->body,
            'published_at' => $post->published_at?->toIso8601String(),
            'author' => [
                'id' => $post->author?->id,
                'username' => $post->author?->username,
            ],
            'counts' => [
                'comments' => $post->relationLoaded('comments') ? $post->comments->count() : 0,
                'reactions' => $post->relationLoaded('reactions') ? $post->reactions->count() : 0,
            ],
            'hashtags' => $post->toSearchableArray()['hashtags'] ?? [],
            'media' => $post->relationLoaded('media')
                ? $post->media->map(static fn ($media): array => [
                    'id' => $media->id,
                    'url' => $media->url,
                    'kind' => $media->kind,
                    'original_name' => $media->original_name,
                ])->values()->all()
                : [],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function serializeUser($user, ?string $viewerId): ?array
    {
        if ($viewerId !== null && $this->blockRepository->eitherBlocked($viewerId, $user->id)) {
            return null;
        }

        return ($this->buildUserPayload)($user);
    }
}
