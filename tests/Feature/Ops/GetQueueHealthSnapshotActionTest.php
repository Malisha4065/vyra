<?php

use App\Support\Ops\GetQueueHealthSnapshotAction;
use Illuminate\Support\Facades\Redis;

it('builds a queue health snapshot from horizon queue configuration', function () {
    config()->set('queue.default', 'redis');
    config()->set('horizon.environments.testing', [
        'supervisor-realtime' => [
            'queue' => ['notifications', 'communication'],
        ],
        'supervisor-feed' => [
            'queue' => ['feed'],
        ],
    ]);

    Redis::shouldReceive('llen')->once()->with('queues:notifications')->andReturn(2);
    Redis::shouldReceive('zcard')->once()->with('queues:notifications:reserved')->andReturn(1);
    Redis::shouldReceive('zcard')->once()->with('queues:notifications:delayed')->andReturn(0);
    Redis::shouldReceive('llen')->once()->with('queues:communication')->andReturn(4);
    Redis::shouldReceive('zcard')->once()->with('queues:communication:reserved')->andReturn(0);
    Redis::shouldReceive('zcard')->once()->with('queues:communication:delayed')->andReturn(1);
    Redis::shouldReceive('llen')->once()->with('queues:feed')->andReturn(3);
    Redis::shouldReceive('zcard')->once()->with('queues:feed:reserved')->andReturn(1);
    Redis::shouldReceive('zcard')->once()->with('queues:feed:delayed')->andReturn(2);

    $snapshot = app(GetQueueHealthSnapshotAction::class)();

    expect($snapshot['status'])->toBe('ok')
        ->and($snapshot['connection'])->toBe('redis')
        ->and($snapshot['queues'])->toHaveCount(3)
        ->and($snapshot['queues'][0]['queue'])->toBe('notifications')
        ->and($snapshot['queues'][2]['delayed'])->toBe(2);
});

it('marks non-redis queue connections as unsupported', function () {
    config()->set('queue.default', 'sync');

    $snapshot = app(GetQueueHealthSnapshotAction::class)();

    expect($snapshot['status'])->toBe('unsupported')
        ->and($snapshot['message'])->toContain('QUEUE_CONNECTION=redis');
});
