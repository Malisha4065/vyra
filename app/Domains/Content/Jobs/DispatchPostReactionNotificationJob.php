<?php

namespace App\Domains\Content\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DispatchPostReactionNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $postId,
        public readonly string $reactorId,
        public readonly string $reactionType,
    ) {}

    public function handle(): void
    {
        // Notification fan-out will be implemented in Notification domain.
    }
}
