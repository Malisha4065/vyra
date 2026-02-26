<?php

namespace App\Domains\Feed\Data;

use Spatie\LaravelData\Data;

class RemovePostFromFeedsData extends Data
{
    public function __construct(
        public readonly string $post_id,
        public readonly string $author_id,
    ) {}
}
