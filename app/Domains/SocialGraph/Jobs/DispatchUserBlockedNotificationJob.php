<?php

namespace App\Domains\SocialGraph\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DispatchUserBlockedNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $blockerId,
        public readonly string $blockedId,
    ) {}

    public function handle(): void
    {
        // Notification fan-out will be implemented in Notification domain.
    }
}
