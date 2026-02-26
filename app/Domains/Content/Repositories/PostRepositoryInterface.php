<?php

namespace App\Domains\Content\Repositories;

use App\Domains\Content\Models\Post;

interface PostRepositoryInterface
{
    /**
     * @param array<int, array{url: string, mime_type?: string|null, size_bytes?: int|null, kind?: string|null}> $media
     */
    public function publish(string $userId, ?string $body, array $media): Post;

    public function findById(string $id): ?Post;

    public function updateBody(Post $post, string $body): Post;

    public function delete(Post $post): void;
}
