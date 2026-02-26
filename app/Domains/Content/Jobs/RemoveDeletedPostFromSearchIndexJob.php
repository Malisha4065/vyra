<?php

namespace App\Domains\Content\Jobs;

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
        // Search de-indexing will be implemented when Scout projections are expanded.
    }
}
