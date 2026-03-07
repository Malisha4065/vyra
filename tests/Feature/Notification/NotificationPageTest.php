<?php

use App\Domains\Identity\Models\User;
use App\Domains\Notification\Models\UserNotificationPreference;
use App\Domains\Notification\Repositories\NotificationPreferenceRepositoryInterface;
use App\Domains\Notification\Repositories\UserNotificationRepositoryInterface;
use Inertia\Testing\AssertableInertia as Assert;

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

    $preference = new UserNotificationPreference();
    $preference->forceFill([
        'id' => 'preference-1',
        'user_id' => 'user-1',
        'social_enabled' => true,
        'content_enabled' => true,
        'communication_enabled' => false,
        'account_enabled' => true,
    ]);

    $preferenceRepository = mock(NotificationPreferenceRepositoryInterface::class);
    $preferenceRepository->shouldReceive('firstOrCreateByUserId')
        ->once()
        ->with('user-1')
        ->andReturn($preference);

    $this->app->instance(UserNotificationRepositoryInterface::class, $repository);
    $this->app->instance(NotificationPreferenceRepositoryInterface::class, $preferenceRepository);

    $response = $this->actingAs($user)->get(route('notifications.page'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Notification/Index')
        ->where('preferences.communication_enabled', false)
        ->where('preferences.social_enabled', true)
    );
});
