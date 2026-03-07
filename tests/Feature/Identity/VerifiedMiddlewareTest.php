<?php

use App\Domains\Identity\Models\User;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Http\Request;

it('assigns the verified middleware to product routes', function () {
    $route = app('router')->getRoutes()->getByName('feed');

    expect($route?->gatherMiddleware() ?? [])->toContain('verified');
});

it('redirects unverified users to the verification notice in middleware', function () {
    $middleware = new EnsureEmailIsVerified();

    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'alice',
        'email' => 'alice@example.com',
        'password' => 'secret',
        'email_verified_at' => null,
    ]);

    $request = Request::create(route('feed'), 'GET', [], [], [], [
        'HTTP_ACCEPT' => 'text/html',
    ]);
    $request->setUserResolver(fn () => $user);

    $response = $middleware->handle($request, fn () => response('ok'));

    expect($response->getTargetUrl())->toBe(route('verification.notice'));
});

it('allows verified users through the verified middleware', function () {
    $middleware = new EnsureEmailIsVerified();

    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'alice',
        'email' => 'alice@example.com',
        'password' => 'secret',
        'email_verified_at' => now(),
    ]);

    $request = Request::create(route('feed'), 'GET', [], [], [], [
        'HTTP_ACCEPT' => 'text/html',
    ]);
    $request->setUserResolver(fn () => $user);

    $response = $middleware->handle($request, fn () => response('ok'));

    expect($response->getContent())->toBe('ok');
});
