<?php

use App\Domains\Identity\Models\User;
use App\Domains\Identity\Repositories\UserRepositoryInterface;
use App\Domains\Notification\Actions\BuildUserNotificationPayloadAction;
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

    $actor = new User();
    $actor->forceFill([
        'id' => 'user-2',
        'username' => 'bob',
        'email' => 'bob@example.com',
        'password' => 'secret',
    ]);

    $userRepository = mock(UserRepositoryInterface::class);
    $userRepository->shouldReceive('findById')->once()->with('user-2')->andReturn($actor);

    $job = new BroadcastUserNotificationJob('notification-1');
    $job->handle($repository, new BuildUserNotificationPayloadAction($userRepository));

    Event::assertDispatched(UserNotificationBroadcast::class, function (UserNotificationBroadcast $event): bool {
        return $event->userId === 'user-1'
            && $event->notification['id'] === 'notification-1'
            && $event->notification['type'] === 'social.followed'
            && $event->notification['action_url'] === route('profile.show', ['username' => 'bob']);
    });
});

it('skips broadcasting when notification is missing', function () {
    Event::fake();

    $repository = mock(UserNotificationRepositoryInterface::class);
    $repository->shouldReceive('findById')->once()->with('missing-notification')->andReturn(null);

    $userRepository = mock(UserRepositoryInterface::class);
    $userRepository->shouldReceive('findById')->never();

    $job = new BroadcastUserNotificationJob('missing-notification');
    $job->handle($repository, new BuildUserNotificationPayloadAction($userRepository));

    Event::assertNotDispatched(UserNotificationBroadcast::class);
});
