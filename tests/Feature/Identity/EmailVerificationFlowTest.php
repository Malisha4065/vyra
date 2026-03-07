<?php

use App\Domains\Identity\Actions\RequestEmailVerificationAction;
use App\Domains\Identity\Actions\RegisterUserAction;
use App\Domains\Identity\Actions\VerifyEmailAction;
use App\Domains\Identity\Data\LoginData;
use App\Domains\Identity\Data\RegisterUserData;
use App\Domains\Identity\Exceptions\InvalidCredentialsException;
use App\Domains\Identity\Models\User;
use App\Domains\Identity\Repositories\UserRepositoryInterface;
use App\Domains\Notification\Repositories\UserNotificationRepositoryInterface;
use App\Http\Controllers\Identity\RegisterController;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;

it('redirects newly registered users to the verification notice', function () {
    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'alice',
        'email' => 'alice@example.com',
        'password' => 'secret',
        'email_verified_at' => null,
    ]);

    $action = mock(RegisterUserAction::class);
    $action->shouldReceive('__invoke')->once()->andReturn($user);

    $controller = app(RegisterController::class);
    $response = $controller->store(
        new RegisterUserData('alice', 'alice@example.com', 'Password123!', 'Password123!'),
        $action,
    );

    expect($response->getTargetUrl())->toBe(route('verification.notice'));
});

it('redirects unverified users to the verification notice after login', function () {
    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'alice',
        'email' => 'alice@example.com',
        'password' => 'secret',
        'email_verified_at' => null,
    ]);

    $action = mock(\App\Domains\Identity\Actions\LoginUserAction::class);
    $action->shouldReceive('__invoke')
        ->once()
        ->withArgs(fn (LoginData $data): bool => $data->email === 'alice@example.com')
        ->andReturn($user);

    $this->app->instance(\App\Domains\Identity\Actions\LoginUserAction::class, $action);

    $response = $this->post(route('login'), [
        'email' => 'alice@example.com',
        'password' => 'Password123!',
        'remember' => false,
    ]);

    $response->assertRedirect(route('verification.notice'));
});

it('renders the verification notice page for authenticated users', function () {
    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'alice',
        'email' => 'alice@example.com',
        'password' => 'secret',
        'email_verified_at' => null,
    ]);

    $notificationRepository = mock(UserNotificationRepositoryInterface::class);
    $notificationRepository->shouldReceive('unreadCount')->once()->with('user-1')->andReturn(0);
    $this->app->instance(UserNotificationRepositoryInterface::class, $notificationRepository);

    $response = $this->actingAs($user)->get(route('verification.notice'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page->component('Auth/VerifyEmail'));
});

it('re-sends a verification email for an authenticated user', function () {
    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'alice',
        'email' => 'alice@example.com',
        'password' => 'secret',
        'email_verified_at' => null,
    ]);

    $action = mock(RequestEmailVerificationAction::class);
    $action->shouldReceive('__invoke')->once()->with($user);
    $this->app->instance(RequestEmailVerificationAction::class, $action);

    $response = $this->actingAs($user)->post(route('verification.send'));

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Verification email sent.');
});

it('verifies the authenticated user email from a signed link', function () {
    Carbon::setTestNow('2026-03-07 10:00:00');

    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'alice',
        'email' => 'alice@example.com',
        'password' => 'secret',
        'email_verified_at' => null,
    ]);

    $repository = mock(UserRepositoryInterface::class);
    $repository->shouldReceive('findById')->once()->with('user-1')->andReturn($user);
    $this->app->instance(UserRepositoryInterface::class, $repository);

    $action = mock(VerifyEmailAction::class);
    $action->shouldReceive('__invoke')->once()->with($user, sha1('alice@example.com'))->andReturn(true);
    $this->app->instance(VerifyEmailAction::class, $action);

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => 'user-1',
        'hash' => sha1('alice@example.com'),
    ]);

    $response = $this->actingAs($user)->get($url);

    $response->assertRedirect(route('feed'));
    $response->assertSessionHas('success', 'Email verified.');
});

it('rate limits repeated failed login attempts', function () {
    $action = mock(\App\Domains\Identity\Actions\LoginUserAction::class);
    $action->shouldReceive('__invoke')->times(5)->andThrow(new InvalidCredentialsException());
    $this->app->instance(\App\Domains\Identity\Actions\LoginUserAction::class, $action);

    $payload = [
        'email' => 'throttle@example.com',
        'password' => 'WrongPass123!',
        'remember' => false,
    ];

    foreach (range(1, 5) as $attempt) {
        $response = $this->from(route('login'))->post(route('login'), $payload);
        $response->assertRedirect(route('login'));
    }

    $this->post(route('login'), $payload)->assertStatus(429);
});
