<?php

namespace App\Domains\Content\Repositories;

use App\Domains\Content\Models\Post;

interface PostRepositoryInterface
{
    /**
     * @param array<int, array{
     *   url?: string|null,
     *   path?: string|null,
     *   disk?: string|null,
     *   original_name?: string|null,
     *   mime_type?: string|null,
     *   size_bytes?: int|null,
     *   kind?: string|null
     * }> $media
     */
    public function publish(string $userId, ?string $body, array $media): Post;

    public function findById(string $id): ?Post;

    /**
     * @param array<int, string> $ids
     * @return array<int, Post>
     */
    public function findByIds(array $ids): array;

    public function updateBody(Post $post, string $body): Post;

    public function markMediaProcessed(string $postId): int;

    public function delete(Post $post): void;
}
