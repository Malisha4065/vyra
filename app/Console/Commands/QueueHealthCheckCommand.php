<?php

namespace App\Console\Commands;

use App\Support\Ops\GetQueueHealthSnapshotAction;
use Illuminate\Console\Command;

class QueueHealthCheckCommand extends Command
{
    protected $signature = 'ops:queue-health {--json : Output queue health as JSON}';

    protected $description = 'Inspect Redis-backed Horizon queue health and queue backlog sizes.';

    public function handle(GetQueueHealthSnapshotAction $action): int
    {
        $snapshot = $action();

        if ($snapshot['status'] !== 'ok') {
            if ($this->option('json')) {
                $this->line(json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            } else {
                $this->warn($snapshot['message']);
                $this->line("Current connection: {$snapshot['connection']}");
            }

            return self::SUCCESS;
        }

        if ($this->option('json')) {
            $this->line(json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->info('Queue Health');
        $this->table(['Queue', 'Pending', 'Reserved', 'Delayed'], $snapshot['queues']);

        return self::SUCCESS;
    }
}
