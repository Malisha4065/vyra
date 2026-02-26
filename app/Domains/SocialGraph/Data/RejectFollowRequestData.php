<?php

namespace App\Domains\SocialGraph\Data;

use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Data;

class RejectFollowRequestData extends Data
{
    public function __construct(
        #[Exists('follow_requests', 'id')]
        public readonly string $request_id,
    ) {}
}
