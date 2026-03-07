<?php

use App\Domains\Identity\Models\User;
use App\Domains\Notification\Repositories\UserNotificationRepositoryInterface;

it('renders notification center page for authenticated user', function () {
    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'owner',
        'email' => 'owner@example.com',
        'password' => 'secret',
    ]);

    $repository = mock(UserNotificationRepositoryInterface::class);
    $repository->shouldReceive('unreadCount')
        ->once()
        ->with('user-1')
        ->andReturn(5);

    $this->app->instance(UserNotificationRepositoryInterface::class, $repository);

    $response = $this->actingAs($user)->get(route('notifications.page'));

    $response->assertOk();
});
