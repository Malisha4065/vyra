<?php

namespace App\Domains\Feed\Data;

use Spatie\LaravelData\Data;

class FeedPostData extends Data
{
    /**
     * @param array{comments: int, reactions: int} $counts
     */
    public function __construct(
        public readonly string $id,
        public readonly string $user_id,
        public readonly ?string $body,
        public readonly ?string $published_at,
        public readonly FeedAuthorData $author,
        public readonly array $counts,
    ) {}
}
