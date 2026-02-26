<?php

namespace App\Domains\Content\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DispatchPostCommentNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $commentId,
        public readonly string $postId,
        public readonly string $authorId,
    ) {}

    public function handle(): void
    {
        // Notification fan-out will be implemented in Notification domain.
    }
}
