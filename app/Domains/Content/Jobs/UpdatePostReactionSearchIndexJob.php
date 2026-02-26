<?php

namespace App\Domains\Content\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdatePostReactionSearchIndexJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $postId,
        public readonly string $reactorId,
        public readonly string $reactionType,
        public readonly string $action,
    ) {}

    public function handle(): void
    {
        // Search indexing will be implemented when Scout projections are expanded.
    }
}
