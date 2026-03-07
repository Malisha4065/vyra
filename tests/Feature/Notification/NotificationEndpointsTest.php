<?php

use App\Domains\Identity\Models\User;
use App\Domains\Notification\Models\UserNotificationPreference;
use App\Domains\Notification\Repositories\NotificationPreferenceRepositoryInterface;
use App\Domains\Notification\Models\UserNotification;
use App\Domains\Notification\Repositories\UserNotificationRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

it('returns paginated notifications for authenticated user', function () {
    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'viewer',
        'email' => 'viewer@example.com',
        'password' => 'secret',
    ]);

    $repository = mock(UserNotificationRepositoryInterface::class);
    $repository->shouldReceive('paginateForUser')
        ->once()
        ->with('user-1', 5, true)
        ->andReturn(new LengthAwarePaginator(
            items: [
                [
                    'id' => 'notification-1',
                    'type' => 'social.followed',
                    'title' => 'New follower',
                ],
            ],
            total: 1,
            perPage: 5,
            currentPage: 1,
        ));
    $repository->shouldReceive('unreadCount')
        ->once()
        ->with('user-1')
        ->andReturn(4);

    $this->app->instance(UserNotificationRepositoryInterface::class, $repository);

    $response = $this->actingAs($user)->get(route('notifications.index', [
        'per_page' => 5,
        'unread_only' => 1,
    ]));

    $response->assertOk();
    $response->assertJsonPath('meta.total', 1);
    $response->assertJsonPath('meta.unread_total', 4);
    $response->assertJsonPath('data.0.id', 'notification-1');
});

it('marks an owned notification as read via endpoint', function () {
    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'owner',
        'email' => 'owner@example.com',
        'password' => 'secret',
    ]);

    $notification = new UserNotification();
    $notification->forceFill([
        'id' => 'notification-1',
        'user_id' => 'user-1',
        'type' => 'social.followed',
        'title' => 'New follower',
    ]);

    $readNotification = new UserNotification();
    $readNotification->forceFill([
        'id' => 'notification-1',
        'user_id' => 'user-1',
        'type' => 'social.followed',
        'title' => 'New follower',
        'read_at' => Carbon::parse('2026-01-01T00:00:00Z'),
    ]);

    $repository = mock(UserNotificationRepositoryInterface::class);
    $repository->shouldReceive('findById')
        ->once()
        ->with('notification-1')
        ->andReturn($notification);
    $repository->shouldReceive('markAsRead')
        ->once()
        ->with($notification)
        ->andReturn($readNotification);

    $this->app->instance(UserNotificationRepositoryInterface::class, $repository);

    $response = $this->actingAs($user)
        ->from('/')
        ->put(route('notifications.read', ['notification' => 'notification-1']));

    $response->assertRedirect('/');
    $response->assertSessionHas('success', 'Notification marked as read.');
});

it('marks an owned notification as read via json endpoint', function () {
    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'owner',
        'email' => 'owner@example.com',
        'password' => 'secret',
    ]);

    $notification = new UserNotification();
    $notification->forceFill([
        'id' => 'notification-1',
        'user_id' => 'user-1',
        'type' => 'social.followed',
        'title' => 'New follower',
    ]);

    $readNotification = new UserNotification();
    $readNotification->forceFill([
        'id' => 'notification-1',
        'user_id' => 'user-1',
        'type' => 'social.followed',
        'title' => 'New follower',
        'read_at' => Carbon::parse('2026-01-01T00:00:00Z'),
    ]);

    $repository = mock(UserNotificationRepositoryInterface::class);
    $repository->shouldReceive('findById')
        ->once()
        ->with('notification-1')
        ->andReturn($notification);
    $repository->shouldReceive('markAsRead')
        ->once()
        ->with($notification)
        ->andReturn($readNotification);
    $repository->shouldReceive('unreadCount')
        ->once()
        ->with('user-1')
        ->andReturn(2);

    $this->app->instance(UserNotificationRepositoryInterface::class, $repository);

    $response = $this->actingAs($user)
        ->withHeaders(['Accept' => 'application/json'])
        ->put(route('notifications.read', ['notification' => 'notification-1']));

    $response->assertOk();
    $response->assertJsonPath('data.id', 'notification-1');
    $response->assertJsonPath('meta.unread_total', 2);
});

it('updates notification preferences via json endpoint', function () {
    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'owner',
        'email' => 'owner@example.com',
        'password' => 'secret',
    ]);

    $preference = new UserNotificationPreference();
    $preference->forceFill([
        'id' => 'preference-1',
        'user_id' => 'user-1',
        'social_enabled' => true,
        'content_enabled' => false,
        'communication_enabled' => true,
        'account_enabled' => false,
    ]);

    $repository = mock(NotificationPreferenceRepositoryInterface::class);
    $repository->shouldReceive('updateByUserId')
        ->once()
        ->with('user-1', [
            'social_enabled' => true,
            'content_enabled' => false,
            'communication_enabled' => true,
            'account_enabled' => false,
        ])
        ->andReturn($preference);

    $this->app->instance(NotificationPreferenceRepositoryInterface::class, $repository);

    $response = $this->actingAs($user)
        ->withHeaders(['Accept' => 'application/json'])
        ->put(route('notifications.preferences.update'), [
            'social_enabled' => true,
            'content_enabled' => false,
            'communication_enabled' => true,
            'account_enabled' => false,
        ]);

    $response->assertOk();
    $response->assertJsonPath('data.content_enabled', false);
    $response->assertJsonPath('data.account_enabled', false);
});

it('returns 404 when marking read for unknown notification', function () {
    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'owner',
        'email' => 'owner@example.com',
        'password' => 'secret',
    ]);

    $repository = mock(UserNotificationRepositoryInterface::class);
    $repository->shouldReceive('findById')
        ->once()
        ->with('missing-notification')
        ->andReturn(null);
    $repository->shouldReceive('markAsRead')->never();

    $this->app->instance(UserNotificationRepositoryInterface::class, $repository);

    $response = $this->actingAs($user)
        ->put(route('notifications.read', ['notification' => 'missing-notification']));

    $response->assertNotFound();
});

it('forbids marking notification read when it belongs to another user', function () {
    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'owner',
        'email' => 'owner@example.com',
        'password' => 'secret',
    ]);

    $notification = new UserNotification();
    $notification->forceFill([
        'id' => 'notification-2',
        'user_id' => 'user-2',
        'type' => 'social.followed',
        'title' => 'New follower',
    ]);

    $repository = mock(UserNotificationRepositoryInterface::class);
    $repository->shouldReceive('findById')
        ->once()
        ->with('notification-2')
        ->andReturn($notification);
    $repository->shouldReceive('markAsRead')->never();

    $this->app->instance(UserNotificationRepositoryInterface::class, $repository);

    $response = $this->actingAs($user)
        ->put(route('notifications.read', ['notification' => 'notification-2']));

    $response->assertForbidden();
});

it('marks all notifications as read for authenticated user', function () {
    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'owner',
        'email' => 'owner@example.com',
        'password' => 'secret',
    ]);

    $repository = mock(UserNotificationRepositoryInterface::class);
    $repository->shouldReceive('markAllAsRead')
        ->once()
        ->with('user-1')
        ->andReturn(3);

    $this->app->instance(UserNotificationRepositoryInterface::class, $repository);

    $response = $this->actingAs($user)
        ->from('/')
        ->put(route('notifications.read-all'));

    $response->assertRedirect('/');
    $response->assertSessionHas('success', '3 notifications marked as read.');
});

it('marks all notifications as read via json endpoint', function () {
    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'owner',
        'email' => 'owner@example.com',
        'password' => 'secret',
    ]);

    $repository = mock(UserNotificationRepositoryInterface::class);
    $repository->shouldReceive('markAllAsRead')
        ->once()
        ->with('user-1')
        ->andReturn(3);
    $repository->shouldReceive('unreadCount')
        ->once()
        ->with('user-1')
        ->andReturn(0);

    $this->app->instance(UserNotificationRepositoryInterface::class, $repository);

    $response = $this->actingAs($user)
        ->withHeaders(['Accept' => 'application/json'])
        ->put(route('notifications.read-all'));

    $response->assertOk();
    $response->assertJsonPath('data.marked_count', 3);
    $response->assertJsonPath('meta.unread_total', 0);
});
