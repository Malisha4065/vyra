<?php

namespace App\Domains\Feed\Data;

use Spatie\LaravelData\Data;

class FeedItemData extends Data
{
    public function __construct(
        public readonly string $post_id,
        public readonly int $score,
        public readonly FeedPostData $post,
    ) {}
}
