<?php

use App\Domains\Identity\Actions\RequestPasswordResetLinkAction;
use App\Domains\Identity\Actions\ResetPasswordAction;
use App\Domains\Identity\Data\RequestPasswordResetLinkData;
use App\Domains\Identity\Data\ResetPasswordData;
use App\Domains\Identity\Exceptions\InvalidPasswordResetTokenException;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the forgot password page', function () {
    $this->get(route('password.request'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Auth/ForgotPassword'));
});

it('requests a password reset link through the identity action', function () {
    $action = mock(RequestPasswordResetLinkAction::class);
    $action->shouldReceive('__invoke')
        ->once()
        ->withArgs(fn (RequestPasswordResetLinkData $data): bool => $data->email === 'alice@example.com');

    $this->app->instance(RequestPasswordResetLinkAction::class, $action);

    $response = $this->from(route('password.request'))->post(route('password.email'), [
        'email' => 'alice@example.com',
    ]);

    $response->assertRedirect(route('password.request'));
    $response->assertSessionHas('success', 'If that account exists, a password reset link has been sent.');
});

it('renders the reset password page', function () {
    $this->get(route('password.reset', [
        'token' => 'reset-token',
        'email' => 'alice@example.com',
    ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Auth/ResetPassword')
            ->where('token', 'reset-token')
            ->where('email', 'alice@example.com'));
});

it('resets the password through the identity action', function () {
    $action = mock(ResetPasswordAction::class);
    $action->shouldReceive('__invoke')
        ->once()
        ->withArgs(fn (ResetPasswordData $data): bool => $data->token === 'reset-token');

    $this->app->instance(ResetPasswordAction::class, $action);

    $response = $this->post(route('password.update'), [
        'token' => 'reset-token',
        'email' => 'alice@example.com',
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('success', 'Password reset. You can now sign in.');
});

it('returns an error for an invalid password reset token', function () {
    $action = mock(ResetPasswordAction::class);
    $action->shouldReceive('__invoke')
        ->once()
        ->andThrow(new InvalidPasswordResetTokenException());

    $this->app->instance(ResetPasswordAction::class, $action);

    $response = $this->from(route('password.reset', ['token' => 'bad-token']))
        ->post(route('password.update'), [
            'token' => 'bad-token',
            'email' => 'alice@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

    $response->assertRedirect(route('password.reset', ['token' => 'bad-token']));
    $response->assertSessionHasErrors([
        'email' => 'This password reset link is invalid or has expired.',
    ]);
});
