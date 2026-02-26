<?php

namespace App\Domains\Feed\Data;

use Spatie\LaravelData\Data;

class FanOutPostOnWriteData extends Data
{
    public function __construct(
        public readonly string $post_id,
        public readonly string $author_id,
        public readonly int $published_at,
    ) {}
}
