<?php

use App\Domains\Notification\Events\Broadcast\UserNotificationBroadcast;
use App\Domains\Notification\Jobs\BroadcastUserNotificationJob;
use App\Domains\Notification\Models\UserNotification;
use App\Domains\Notification\Repositories\UserNotificationRepositoryInterface;
use Illuminate\Support\Facades\Event;

it('broadcasts user notification payload when notification exists', function () {
    Event::fake();

    $notification = new UserNotification();
    $notification->forceFill([
        'id' => 'notification-1',
        'user_id' => 'user-1',
        'type' => 'social.followed',
        'title' => 'New follower',
        'body' => 'Someone followed you',
        'data' => ['actor_user_id' => 'user-2'],
        'created_at' => now(),
    ]);

    $repository = mock(UserNotificationRepositoryInterface::class);
    $repository->shouldReceive('findById')->once()->with('notification-1')->andReturn($notification);

    $job = new BroadcastUserNotificationJob('notification-1');
    $job->handle($repository);

    Event::assertDispatched(UserNotificationBroadcast::class, function (UserNotificationBroadcast $event): bool {
        return $event->userId === 'user-1'
            && $event->notification['id'] === 'notification-1'
            && $event->notification['type'] === 'social.followed';
    });
});

it('skips broadcasting when notification is missing', function () {
    Event::fake();

    $repository = mock(UserNotificationRepositoryInterface::class);
    $repository->shouldReceive('findById')->once()->with('missing-notification')->andReturn(null);

    $job = new BroadcastUserNotificationJob('missing-notification');
    $job->handle($repository);

    Event::assertNotDispatched(UserNotificationBroadcast::class);
});
