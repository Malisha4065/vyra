<?php

namespace App\Domains\Content\Jobs;

use App\Domains\Feed\Actions\RemovePostFromFeedsAction;
use App\Domains\Feed\Data\RemovePostFromFeedsData;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RemoveDeletedPostFromFeedCachesJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $postId,
        public readonly string $authorId,
    ) {}

    public function handle(RemovePostFromFeedsAction $removePostFromFeedsAction): void
    {
        $removePostFromFeedsAction(RemovePostFromFeedsData::from([
            'post_id' => $this->postId,
            'author_id' => $this->authorId,
        ]));
    }
}
