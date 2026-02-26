<?php

namespace App\Domains\Content\Jobs;

use App\Domains\Content\Repositories\PostRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotifyFollowersOfPublishedPostJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $postId,
        public readonly string $authorId,
    ) {}

    public function handle(PostRepositoryInterface $postRepository): void
    {
        $post = $postRepository->findById($this->postId);

        if ($post === null) {
            return;
        }

        // Feed fan-out and follower notifications will be implemented here.
    }
}
