<?php

namespace App\Domains\Content\Actions;

use App\Domains\Content\Data\DeletePostData;
use App\Domains\Content\Events\PostDeleted;
use App\Domains\Content\Models\Post;
use App\Domains\Content\Repositories\PostRepositoryInterface;

class DeletePostAction
{
    public function __construct(
        private readonly PostRepositoryInterface $postRepository,
    ) {}

    public function __invoke(Post $post, DeletePostData $data): void
    {
        if ($post->id !== $data->post_id) {
            return;
        }

        $payload = [
            'post_id' => $post->id,
            'author_id' => $post->user_id,
        ];

        $this->postRepository->delete($post);

        event(new PostDeleted(
            postId: $payload['post_id'],
            authorId: $payload['author_id'],
        ));
    }
}
