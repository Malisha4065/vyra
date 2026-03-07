<?php

use Illuminate\Support\Str;

return [

    'domain' => env('HORIZON_DOMAIN'),

    'path' => env('HORIZON_PATH', 'horizon'),

    'use' => 'default',

    'prefix' => env('HORIZON_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_horizon:'),

    'allowed_emails' => array_values(array_filter(array_map(
        static fn (string $email): string => trim($email),
        explode(',', (string) env('HORIZON_ALLOWED_EMAILS', ''))
    ))),

    'middleware' => ['web'],

    'waits' => [
        'redis:default' => 60,
    ],

    'trim' => [
        'recent' => 60,
        'pending' => 60,
        'completed' => 60,
        'recent_failed' => 10080,
        'failed' => 10080,
        'monitored' => 10080,
    ],

    'silenced' => [],

    'metrics' => [
        'trim_snapshots' => [
            'job' => 24,
            'queue' => 24,
        ],
    ],

    'fast_termination' => false,

    'memory_limit' => 256,

    'defaults' => [
        'supervisor-default' => [
            'connection' => 'redis',
            'queue' => ['default'],
            'balance' => 'auto',
            'autoScalingStrategy' => 'time',
            'maxProcesses' => 1,
            'maxTime' => 0,
            'maxJobs' => 0,
            'memory' => 256,
            'tries' => 1,
            'timeout' => 90,
            'nice' => 0,
        ],
    ],

    'environments' => [
        'production' => [
            'supervisor-realtime' => [
                'connection' => 'redis',
                'queue' => ['notifications', 'communication'],
                'balance' => 'auto',
                'maxProcesses' => 6,
                'tries' => 1,
                'timeout' => 60,
            ],
            'supervisor-feed' => [
                'connection' => 'redis',
                'queue' => ['feed'],
                'balance' => 'auto',
                'maxProcesses' => 4,
                'tries' => 1,
                'timeout' => 180,
            ],
            'supervisor-search' => [
                'connection' => 'redis',
                'queue' => ['search'],
                'balance' => 'auto',
                'maxProcesses' => 3,
                'tries' => 1,
                'timeout' => 120,
            ],
            'supervisor-media' => [
                'connection' => 'redis',
                'queue' => ['media'],
                'balance' => 'auto',
                'maxProcesses' => 2,
                'tries' => 1,
                'timeout' => 600,
            ],
            'supervisor-default' => [
                'connection' => 'redis',
                'queue' => ['default'],
                'balance' => 'auto',
                'maxProcesses' => 2,
                'tries' => 1,
                'timeout' => 90,
            ],
        ],

        'local' => [
            'supervisor-realtime' => [
                'connection' => 'redis',
                'queue' => ['notifications', 'communication'],
                'balance' => 'simple',
                'maxProcesses' => 2,
                'tries' => 1,
                'timeout' => 60,
            ],
            'supervisor-feed' => [
                'connection' => 'redis',
                'queue' => ['feed'],
                'balance' => 'simple',
                'maxProcesses' => 1,
                'tries' => 1,
                'timeout' => 180,
            ],
            'supervisor-search' => [
                'connection' => 'redis',
                'queue' => ['search'],
                'balance' => 'simple',
                'maxProcesses' => 1,
                'tries' => 1,
                'timeout' => 120,
            ],
            'supervisor-media' => [
                'connection' => 'redis',
                'queue' => ['media'],
                'balance' => 'simple',
                'maxProcesses' => 1,
                'tries' => 1,
                'timeout' => 600,
            ],
            'supervisor-default' => [
                'connection' => 'redis',
                'queue' => ['default'],
                'balance' => 'simple',
                'maxProcesses' => 1,
                'tries' => 1,
                'timeout' => 90,
            ],
        ],
    ],
];
