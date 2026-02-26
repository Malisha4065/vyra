<?php

namespace App\Domains\Content\Repositories;

use App\Domains\Content\Models\Post;
use App\Domains\Content\Models\PostMedia;
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
                    'url' => $item['url'],
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

    public function updateBody(Post $post, string $body): Post
    {
        $post->update([
            'body' => $body,
        ]);

        return $this->findById($post->id) ?? $post;
    }

    public function delete(Post $post): void
    {
        $post->delete();
    }
}
