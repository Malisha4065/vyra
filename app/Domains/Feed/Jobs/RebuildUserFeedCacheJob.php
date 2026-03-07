<?php

namespace App\Domains\Feed\Jobs;

use App\Domains\Feed\Actions\RebuildUserFeedCacheAction;
use App\Domains\Feed\Data\RebuildUserFeedCacheData;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RebuildUserFeedCacheJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly RebuildUserFeedCacheData $data,
    ) {}

    public function handle(RebuildUserFeedCacheAction $action): void
    {
        $action($this->data);
    }
}
