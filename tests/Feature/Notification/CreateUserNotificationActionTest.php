<?php

use App\Domains\Notification\Actions\CreateUserNotificationAction;
use App\Domains\Notification\Data\CreateUserNotificationData;
use App\Domains\Notification\Events\UserNotificationCreated;
use App\Domains\Notification\Models\UserNotification;
use App\Domains\Notification\Repositories\NotificationPreferenceRepositoryInterface;
use App\Domains\Notification\Repositories\UserNotificationRepositoryInterface;
use Illuminate\Support\Facades\Event;

it('creates a notification and dispatches notification-created event', function () {
    Event::fake();

    $notification = new UserNotification();
    $notification->forceFill([
        'id' => 'notification-1',
        'user_id' => 'user-2',
        'type' => 'social.followed',
        'title' => 'New follower',
        'body' => 'Someone followed you.',
        'data' => ['actor_user_id' => 'user-1'],
    ]);

    $repository = mock(UserNotificationRepositoryInterface::class);
    $repository->shouldReceive('create')
        ->once()
        ->with([
            'user_id' => 'user-2',
            'type' => 'social.followed',
            'title' => 'New follower',
            'body' => 'Someone followed you.',
            'data' => ['actor_user_id' => 'user-1'],
        ])
        ->andReturn($notification);

    $preferences = mock(NotificationPreferenceRepositoryInterface::class);
    $preferences->shouldReceive('allowsType')
        ->once()
        ->with('user-2', 'social.followed')
        ->andReturn(true);

    $action = new CreateUserNotificationAction($repository, $preferences);

    $result = $action(CreateUserNotificationData::from([
        'user_id' => 'user-2',
        'type' => 'social.followed',
        'title' => 'New follower',
        'body' => 'Someone followed you.',
        'data' => ['actor_user_id' => 'user-1'],
    ]));

    expect($result->id)->toBe('notification-1');

    Event::assertDispatched(UserNotificationCreated::class, function (UserNotificationCreated $event): bool {
        return $event->notification->id === 'notification-1';
    });
});

it('skips notification creation when preference category is disabled', function () {
    Event::fake();

    $repository = mock(UserNotificationRepositoryInterface::class);
    $repository->shouldReceive('create')->never();

    $preferences = mock(NotificationPreferenceRepositoryInterface::class);
    $preferences->shouldReceive('allowsType')
        ->once()
        ->with('user-2', 'content.post_reacted')
        ->andReturn(false);

    $action = new CreateUserNotificationAction($repository, $preferences);

    $result = $action(CreateUserNotificationData::from([
        'user_id' => 'user-2',
        'type' => 'content.post_reacted',
        'title' => 'New reaction',
        'body' => 'A post was liked.',
    ]));

    expect($result)->toBeNull();
    Event::assertNotDispatched(UserNotificationCreated::class);
});
