<?php

namespace App\Domains\SocialGraph\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DispatchUserFollowedNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $followerId,
        public readonly string $followeeId,
    ) {}

    public function handle(): void
    {
        // Notification fan-out will be implemented in Notification domain.
    }
}
