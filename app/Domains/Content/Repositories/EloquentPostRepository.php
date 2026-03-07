<?php

namespace App\Domains\Content\Repositories;

use App\Domains\Content\Models\Post;
use App\Domains\Content\Models\PostMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class EloquentPostRepository implements PostRepositoryInterface
{
    public function __construct(
        private readonly Post $postModel,
        private readonly PostMedia $postMediaModel,
    ) {}

    public function publish(string $userId, ?string $body, array $media): Post
    {
        /** @var Post $post */
        $post = DB::transaction(function () use ($userId, $body, $media): Post {
            $post = $this->postModel->create([
                'user_id' => $userId,
                'body' => $body,
                'published_at' => now(),
            ]);

            foreach ($media as $index => $item) {
                $this->postMediaModel->create([
                    'post_id' => $post->id,
                    'url' => $item['url'] ?? null,
                    'disk' => $item['disk'] ?? null,
                    'path' => $item['path'] ?? null,
                    'original_name' => $item['original_name'] ?? null,
                    'mime_type' => $item['mime_type'] ?? null,
                    'size_bytes' => $item['size_bytes'] ?? null,
                    'kind' => $item['kind'] ?? null,
                    'position' => $index + 1,
                ]);
            }

            return $post;
        });

        return $this->findById($post->id) ?? $post;
    }

    public function findById(string $id): ?Post
    {
        return $this->postModel
            ->with(['author.profile', 'media', 'comments.author.profile', 'reactions'])
            ->find($id);
    }

    public function findByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $orderMap = array_flip($ids);

        return $this->postModel
            ->with(['author.profile', 'media', 'comments.author.profile', 'reactions'])
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(static fn (Post $post): int => $orderMap[$post->id] ?? PHP_INT_MAX)
            ->values()
            ->all();
    }

    public function getRecentPublishedPostIdsByAuthors(array $authorIds, int $limit = 500): array
    {
        if ($authorIds === []) {
            return [];
        }

        return $this->postModel
            ->whereIn('user_id', $authorIds)
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get(['id', 'published_at'])
            ->map(static fn (Post $post): array => [
                'post_id' => $post->id,
                'score' => $post->published_at?->timestamp ?? 0,
            ])
            ->all();
    }

    public function searchPublished(string $query, int $limit = 20): array
    {
        $term = mb_strtolower(trim($query));

        if ($term === '') {
            return [];
        }

        $like = '%'.$term.'%';

        return $this->basePublishedQuery()
            ->where(function (Builder $builder) use ($like): void {
                $builder->whereRaw('LOWER(body) LIKE ?', [$like])
                    ->orWhereHas('author', fn (Builder $authorQuery) => $authorQuery->whereRaw('LOWER(username) LIKE ?', [$like]));
            })
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get()
            ->all();
    }

    public function findPublishedByHashtag(string $hashtag, int $limit = 20): array
    {
        $normalized = mb_strtolower(ltrim(trim($hashtag), '#'));

        if ($normalized === '') {
            return [];
        }

        return $this->basePublishedQuery()
            ->whereRaw('LOWER(body) LIKE ?', ['%#'.$normalized.'%'])
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get()
            ->all();
    }

    public function getTrendingHashtags(int $limit = 10): array
    {
        $posts = $this->postModel
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->limit(250)
            ->get(['body']);

        $counts = [];

        foreach ($posts as $post) {
            preg_match_all('/#([\p{L}\p{N}_]+)/u', (string) $post->body, $matches);

            foreach (array_unique(array_map('mb_strtolower', $matches[1] ?? [])) as $tag) {
                $counts[$tag] = ($counts[$tag] ?? 0) + 1;
            }
        }

        arsort($counts);

        $results = [];

        foreach ($counts as $tag => $count) {
            $results[] = [
                'tag' => $tag,
                'count' => $count,
            ];

            if (count($results) >= $limit) {
                break;
            }
        }

        return $results;
    }

    public function updateBody(Post $post, string $body): Post
    {
        $post->update([
            'body' => $body,
        ]);

        return $this->findById($post->id) ?? $post;
    }

    public function markMediaProcessed(string $postId): int
    {
        return $this->postMediaModel
            ->where('post_id', $postId)
            ->whereNull('processed_at')
            ->update([
                'processed_at' => now(),
            ]);
    }

    public function delete(Post $post): void
    {
        $post->delete();
    }

    private function basePublishedQuery(): Builder
    {
        return $this->postModel
            ->with(['author.profile', 'media', 'comments.author.profile', 'reactions'])
            ->whereNotNull('published_at');
    }
}
