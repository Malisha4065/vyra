<?php

use App\Domains\Identity\Models\User;
use App\Domains\Identity\Repositories\UserRepositoryInterface;
use App\Domains\Notification\Actions\BuildUserNotificationPayloadAction;
use App\Domains\Notification\Models\UserNotification;

it('builds a profile deep link for social notifications with an actor', function () {
    $actor = new User();
    $actor->forceFill([
        'id' => 'user-2',
        'username' => 'bob',
        'email' => 'bob@example.com',
        'password' => 'secret',
    ]);

    $notification = new UserNotification();
    $notification->forceFill([
        'id' => 'notification-1',
        'user_id' => 'user-1',
        'type' => 'social.followed',
        'title' => 'New follower',
        'data' => ['actor_user_id' => 'user-2'],
    ]);

    $userRepository = mock(UserRepositoryInterface::class);
    $userRepository->shouldReceive('findById')->once()->with('user-2')->andReturn($actor);

    $action = new BuildUserNotificationPayloadAction($userRepository);

    $payload = $action($notification);

    expect($payload['action_label'])->toBe('View profile')
        ->and($payload['action_url'])->toBe(route('profile.show', ['username' => 'bob']));
});

it('builds a feed deep link for content notifications', function () {
    $notification = new UserNotification();
    $notification->forceFill([
        'id' => 'notification-2',
        'user_id' => 'user-1',
        'type' => 'content.post_commented',
        'title' => 'New comment',
        'data' => ['post_id' => 'post-9'],
    ]);

    $userRepository = mock(UserRepositoryInterface::class);
    $userRepository->shouldReceive('findById')->never();

    $action = new BuildUserNotificationPayloadAction($userRepository);

    $payload = $action($notification);

    expect($payload['action_label'])->toBe('View post')
        ->and($payload['action_url'])->toBe(route('feed', ['focus_post_id' => 'post-9']));
});
