<?php

namespace App\Domains\Feed\Data;

use Spatie\LaravelData\Data;

class FeedAuthorData extends Data
{
    public function __construct(
        public readonly ?string $id,
        public readonly ?string $username,
    ) {}
}
