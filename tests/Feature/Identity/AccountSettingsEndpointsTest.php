<?php

use App\Domains\Identity\Models\User;
use App\Domains\Identity\Models\UserProfile;
use App\Domains\Identity\Repositories\UserRepositoryInterface;
use App\Domains\Notification\Repositories\UserNotificationRepositoryInterface;
use Illuminate\Support\Facades\Hash;

it('updates the password through the profile settings endpoint', function () {
    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'alice',
        'email' => 'alice@example.com',
        'password' => Hash::make('CurrentPass123!'),
    ]);

    $profile = new UserProfile();
    $profile->forceFill([
        'id' => 'profile-1',
        'user_id' => 'user-1',
        'is_private' => false,
    ]);
    $user->setRelation('profile', $profile);

    $userRepository = mock(UserRepositoryInterface::class);
    $userRepository->shouldReceive('update')
        ->once()
        ->with($user, ['password' => 'NewSecurePass123!'])
        ->andReturn($user);

    $notificationRepository = mock(UserNotificationRepositoryInterface::class);
    $notificationRepository->shouldReceive('unreadCount')->never();

    $this->app->instance(UserRepositoryInterface::class, $userRepository);
    $this->app->instance(UserNotificationRepositoryInterface::class, $notificationRepository);

    $response = $this->actingAs($user)
        ->from(route('profile.edit'))
        ->put(route('profile.password'), [
            'current_password' => 'CurrentPass123!',
            'password' => 'NewSecurePass123!',
            'password_confirmation' => 'NewSecurePass123!',
        ]);

    $response->assertRedirect(route('profile.edit'));
    $response->assertSessionHas('success', 'Password updated.');
});

it('deletes the account through the profile settings endpoint', function () {
    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'alice',
        'email' => 'alice@example.com',
        'password' => Hash::make('CurrentPass123!'),
    ]);

    $profile = new UserProfile();
    $profile->forceFill([
        'id' => 'profile-1',
        'user_id' => 'user-1',
        'is_private' => false,
    ]);
    $user->setRelation('profile', $profile);

    $userRepository = mock(UserRepositoryInterface::class);
    $userRepository->shouldReceive('delete')->once()->with($user);

    $notificationRepository = mock(UserNotificationRepositoryInterface::class);
    $notificationRepository->shouldReceive('unreadCount')->never();

    $this->app->instance(UserRepositoryInterface::class, $userRepository);
    $this->app->instance(UserNotificationRepositoryInterface::class, $notificationRepository);

    $response = $this->actingAs($user)
        ->delete(route('profile.destroy'), [
            'current_password' => 'CurrentPass123!',
        ]);

    $response->assertRedirect(route('login'));
});
