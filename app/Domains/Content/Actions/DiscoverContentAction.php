<?php

namespace App\Domains\Content\Actions;

use App\Domains\Content\Data\SearchContentData;
use App\Domains\Content\Models\Post;
use App\Domains\Content\Repositories\PostRepositoryInterface;

class DiscoverContentAction
{
    public function __construct(
        private readonly PostRepositoryInterface $postRepository,
    ) {}

    /**
     * @return array{results: array<int, array<string, mixed>>, trending_hashtags: array<int, array{tag: string, count: int}>}
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

        return [
            'results' => array_map(fn (Post $post): array => $this->serializePost($post), $posts),
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
}
