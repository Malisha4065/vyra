<?php

use App\Domains\Notification\Events\UserNotificationCreated;
use App\Domains\Notification\Jobs\BroadcastUserNotificationJob;
use App\Domains\Notification\Listeners\QueueBroadcastUserNotificationListener;
use App\Domains\Notification\Models\UserNotification;
use Illuminate\Support\Facades\Bus;

it('dispatches broadcast notification job to notifications queue', function () {
    Bus::fake();

    $notification = new UserNotification();
    $notification->forceFill([
        'id' => 'notification-1',
        'user_id' => 'user-1',
        'type' => 'social.followed',
        'title' => 'New follower',
    ]);

    $listener = new QueueBroadcastUserNotificationListener();
    $listener->handle(new UserNotificationCreated($notification));

    Bus::assertDispatched(BroadcastUserNotificationJob::class, function (BroadcastUserNotificationJob $job): bool {
        return $job->notificationId === 'notification-1'
            && $job->queue === 'notifications';
    });
});

