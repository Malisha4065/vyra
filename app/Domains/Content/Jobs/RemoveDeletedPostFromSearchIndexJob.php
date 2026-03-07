<?php

namespace App\Domains\Content\Jobs;

use App\Domains\Content\Models\Post;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RemoveDeletedPostFromSearchIndexJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $postId,
    ) {}

    public function handle(): void
    {
        $post = new Post();
        $post->forceFill([
            'id' => $this->postId,
        ]);

        $post->unsearchable();
    }
}
