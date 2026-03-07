<?php

use App\Domains\Feed\Actions\RequestUserFeedRebuildAction;
use App\Domains\Feed\Data\RebuildUserFeedCacheData;
use App\Domains\Feed\Jobs\RebuildUserFeedCacheJob;
use Illuminate\Support\Facades\Bus;

it('queues a rebuild job for the authenticated user feed', function () {
    Bus::fake();

    $action = new RequestUserFeedRebuildAction();
    $action(RebuildUserFeedCacheData::from([
        'user_id' => 'user-1',
        'limit' => 250,
    ]));

    Bus::assertDispatched(RebuildUserFeedCacheJob::class, function (RebuildUserFeedCacheJob $job): bool {
        return $job->data->user_id === 'user-1'
            && $job->data->limit === 250
            && $job->queue === 'feed';
    });
});
