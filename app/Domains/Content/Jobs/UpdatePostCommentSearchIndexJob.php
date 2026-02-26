<?php

namespace App\Domains\Content\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdatePostCommentSearchIndexJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $postId,
        public readonly string $commentId,
        public readonly string $action,
    ) {}

    public function handle(): void
    {
        // Search indexing will be implemented when Scout projections are expanded.
    }
}
