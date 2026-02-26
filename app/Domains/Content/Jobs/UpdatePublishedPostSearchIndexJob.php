<?php

namespace App\Domains\Content\Jobs;

use App\Domains\Content\Repositories\PostRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdatePublishedPostSearchIndexJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $postId,
    ) {}

    public function handle(PostRepositoryInterface $postRepository): void
    {
        $post = $postRepository->findById($this->postId);

        if ($post === null) {
            return;
        }

        // Search indexing (Scout + Meilisearch) will be implemented here.
    }
}
