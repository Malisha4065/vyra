<?php

namespace App\Domains\Feed\Actions;

use App\Domains\Feed\Data\RebuildUserFeedCacheData;
use App\Domains\Feed\Jobs\RebuildUserFeedCacheJob;

class RequestUserFeedRebuildAction
{
    public function __invoke(RebuildUserFeedCacheData $data): void
    {
        RebuildUserFeedCacheJob::dispatch($data)->onQueue('feed');
    }
}
