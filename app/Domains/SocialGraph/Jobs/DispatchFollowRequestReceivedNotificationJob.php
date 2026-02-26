<?php

namespace App\Domains\SocialGraph\Jobs;

use App\Domains\Notification\Actions\CreateUserNotificationAction;
use App\Domains\Notification\Data\CreateUserNotificationData;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DispatchFollowRequestReceivedNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $requestId,
        public readonly string $requesterId,
        public readonly string $requesteeId,
    ) {}

    public function handle(CreateUserNotificationAction $createNotificationAction): void
    {
        $createNotificationAction(CreateUserNotificationData::from([
            'user_id' => $this->requesteeId,
            'type' => 'social.follow_request_received',
            'title' => 'New follow request',
            'body' => 'You received a new follow request.',
            'data' => [
                'request_id' => $this->requestId,
                'actor_user_id' => $this->requesterId,
            ],
        ]));
    }
}
