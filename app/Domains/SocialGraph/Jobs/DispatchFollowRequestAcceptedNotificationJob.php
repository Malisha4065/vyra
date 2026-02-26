<?php

namespace App\Domains\SocialGraph\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DispatchFollowRequestAcceptedNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $requestId,
        public readonly string $requesterId,
        public readonly string $requesteeId,
    ) {}

    public function handle(): void
    {
        // Notification fan-out will be implemented in Notification domain.
    }
}
