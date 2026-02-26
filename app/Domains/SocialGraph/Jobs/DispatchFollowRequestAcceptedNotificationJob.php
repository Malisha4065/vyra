<?php

namespace App\Domains\SocialGraph\Jobs;

use App\Domains\Notification\Actions\CreateUserNotificationAction;
use App\Domains\Notification\Data\CreateUserNotificationData;
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

    public function handle(CreateUserNotificationAction $createNotificationAction): void
    {
        $createNotificationAction(CreateUserNotificationData::from([
            'user_id' => $this->requesterId,
            'type' => 'social.follow_request_accepted',
            'title' => 'Follow request accepted',
            'body' => 'Your follow request was accepted.',
            'data' => [
                'request_id' => $this->requestId,
                'actor_user_id' => $this->requesteeId,
            ],
        ]));
    }
}
