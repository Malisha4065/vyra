<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class QueueHealthCheckCommand extends Command
{
    protected $signature = 'ops:queue-health {--json : Output queue health as JSON}';

    protected $description = 'Inspect Redis-backed Horizon queue health and queue backlog sizes.';

    public function handle(): int
    {
        $connection = (string) config('queue.default');
        $queues = $this->resolveQueues();

        if ($connection !== 'redis') {
            $payload = [
                'connection' => $connection,
                'status' => 'unsupported',
                'message' => 'Queue health inspection expects QUEUE_CONNECTION=redis.',
            ];

            if ($this->option('json')) {
                $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            } else {
                $this->warn($payload['message']);
                $this->line("Current connection: {$connection}");
            }

            return self::SUCCESS;
        }

        $rows = [];

        foreach ($queues as $queue) {
            $rows[] = [
                'queue' => $queue,
                'pending' => (int) Redis::llen("queues:{$queue}"),
                'reserved' => (int) Redis::zcard("queues:{$queue}:reserved"),
                'delayed' => (int) Redis::zcard("queues:{$queue}:delayed"),
            ];
        }

        if ($this->option('json')) {
            $this->line(json_encode([
                'connection' => $connection,
                'queues' => $rows,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->info('Queue Health');
        $this->table(['Queue', 'Pending', 'Reserved', 'Delayed'], $rows);

        return self::SUCCESS;
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
