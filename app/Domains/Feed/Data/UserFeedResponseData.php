<?php

namespace App\Domains\Feed\Data;

use Spatie\LaravelData\Data;

class UserFeedResponseData extends Data
{
    /**
     * @param array<int, FeedItemData> $items
     */
    public function __construct(
        public readonly array $items,
        public readonly ?int $next_cursor,
    ) {}
}
