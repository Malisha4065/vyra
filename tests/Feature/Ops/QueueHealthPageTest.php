<?php

use App\Domains\Identity\Models\User;
use App\Domains\Notification\Repositories\UserNotificationRepositoryInterface;
use App\Support\Ops\GetQueueHealthSnapshotAction;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the queue ops page for allowed operators', function () {
    config()->set('horizon.allowed_emails', ['ops@example.com']);

    $user = new User();
    $user->forceFill([
        'id' => 'user-ops',
        'username' => 'ops',
        'email' => 'ops@example.com',
        'password' => 'secret',
    ]);

    $notificationRepository = mock(UserNotificationRepositoryInterface::class);
    $notificationRepository->shouldReceive('unreadCount')->once()->with('user-ops')->andReturn(0);
    $this->app->instance(UserNotificationRepositoryInterface::class, $notificationRepository);

    $action = mock(GetQueueHealthSnapshotAction::class);
    $action->shouldReceive('__invoke')->once()->andReturn([
        'connection' => 'redis',
        'status' => 'ok',
        'queues' => [
            [
                'queue' => 'feed',
                'pending' => 3,
                'reserved' => 1,
                'delayed' => 0,
            ],
        ],
        'generated_at' => now()->toIso8601String(),
    ]);
    $this->app->instance(GetQueueHealthSnapshotAction::class, $action);

    $response = $this->actingAs($user)->get(route('ops.queues'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Ops/Queues')
        ->where('snapshot.status', 'ok')
        ->where('snapshot.queues.0.queue', 'feed')
        ->where('horizonUrl', url(config('horizon.path'))));
});

it('forbids the queue ops page for unauthorized users', function () {
    config()->set('horizon.allowed_emails', ['ops@example.com']);

    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'reader',
        'email' => 'reader@example.com',
        'password' => 'secret',
    ]);

    $notificationRepository = mock(UserNotificationRepositoryInterface::class);
    $notificationRepository->shouldReceive('unreadCount')->never();
    $this->app->instance(UserNotificationRepositoryInterface::class, $notificationRepository);

    $response = $this->actingAs($user)->get(route('ops.queues'));

    $response->assertForbidden();
});
