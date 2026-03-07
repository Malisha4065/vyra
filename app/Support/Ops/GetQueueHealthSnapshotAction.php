<?php

namespace App\Support\Ops;

use Illuminate\Support\Facades\Redis;

class GetQueueHealthSnapshotAction
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(): array
    {
        $connection = (string) config('queue.default');

        if ($connection !== 'redis') {
            return [
                'connection' => $connection,
                'status' => 'unsupported',
                'message' => 'Queue health inspection expects QUEUE_CONNECTION=redis.',
                'queues' => [],
                'generated_at' => now()->toIso8601String(),
            ];
        }

        $queues = [];

        foreach ($this->resolveQueues() as $queue) {
            $queues[] = [
                'queue' => $queue,
                'pending' => (int) Redis::llen("queues:{$queue}"),
                'reserved' => (int) Redis::zcard("queues:{$queue}:reserved"),
                'delayed' => (int) Redis::zcard("queues:{$queue}:delayed"),
            ];
        }

        return [
            'connection' => $connection,
            'status' => 'ok',
            'queues' => $queues,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function resolveQueues(): array
    {
        $environmentConfig = config('horizon.environments.'.app()->environment())
            ?? config('horizon.environments.local')
            ?? [];

        $queues = [];

        foreach ($environmentConfig as $supervisor) {
            foreach (($supervisor['queue'] ?? []) as $queue) {
                $queues[] = (string) $queue;
            }
        }

        $queues = array_values(array_unique($queues));

        return $queues === [] ? ['default'] : $queues;
    }
}
