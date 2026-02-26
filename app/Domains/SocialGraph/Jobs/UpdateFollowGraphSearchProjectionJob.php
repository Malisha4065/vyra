<?php

namespace App\Domains\SocialGraph\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdateFollowGraphSearchProjectionJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $sourceUserId,
        public readonly string $targetUserId,
        public readonly string $action,
    ) {}

    public function handle(): void
    {
        // Search projection updates will be implemented when Scout projections are added.
    }
}
