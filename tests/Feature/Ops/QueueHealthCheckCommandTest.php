<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redis;

it('reports queue health for redis-backed horizon queues', function () {
    config()->set('queue.default', 'redis');
    config()->set('horizon.environments.testing', [
        'supervisor-feed' => [
            'queue' => ['feed', 'search'],
        ],
    ]);

    Redis::shouldReceive('llen')->once()->with('queues:feed')->andReturn(4);
    Redis::shouldReceive('zcard')->once()->with('queues:feed:reserved')->andReturn(1);
    Redis::shouldReceive('zcard')->once()->with('queues:feed:delayed')->andReturn(0);
    Redis::shouldReceive('llen')->once()->with('queues:search')->andReturn(2);
    Redis::shouldReceive('zcard')->once()->with('queues:search:reserved')->andReturn(0);
    Redis::shouldReceive('zcard')->once()->with('queues:search:delayed')->andReturn(1);

    Artisan::call('ops:queue-health', ['--json' => true]);
    $output = Artisan::output();

    expect($output)->toContain('"queue": "feed"')
        ->and($output)->toContain('"pending": 4')
        ->and($output)->toContain('"queue": "search"');
});
