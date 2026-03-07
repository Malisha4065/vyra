<?php

use App\Domains\Identity\Models\User;
use App\Domains\Notification\Repositories\UserNotificationRepositoryInterface;
use Inertia\Testing\AssertableInertia as Assert;

it('renders messages page for authenticated user', function () {
    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'alice',
        'email' => 'alice@example.com',
        'password' => 'secret',
    ]);

    $repository = mock(UserNotificationRepositoryInterface::class);
    $repository->shouldReceive('unreadCount')
        ->once()
        ->with('user-1')
        ->andReturn(0);

    $this->app->instance(UserNotificationRepositoryInterface::class, $repository);

    $response = $this->actingAs($user)->get(route('messages.index'));

    $response->assertOk();
});

it('passes target user id to messages page when requested', function () {
    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'alice',
        'email' => 'alice@example.com',
        'password' => 'secret',
    ]);

    $repository = mock(UserNotificationRepositoryInterface::class);
    $repository->shouldReceive('unreadCount')
        ->once()
        ->with('user-1')
        ->andReturn(0);

    $this->app->instance(UserNotificationRepositoryInterface::class, $repository);

    $response = $this->actingAs($user)->get(route('messages.index', ['target_user_id' => 'user-2']));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Communication/Index')
        ->where('targetUserId', 'user-2')
    );
});

it('passes conversation id to messages page when requested', function () {
    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'alice',
        'email' => 'alice@example.com',
        'password' => 'secret',
    ]);

    $repository = mock(UserNotificationRepositoryInterface::class);
    $repository->shouldReceive('unreadCount')
        ->once()
        ->with('user-1')
        ->andReturn(0);

    $this->app->instance(UserNotificationRepositoryInterface::class, $repository);

    $response = $this->actingAs($user)->get(route('messages.index', ['conversation_id' => 'conversation-1']));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Communication/Index')
        ->where('conversationId', 'conversation-1')
    );
});
