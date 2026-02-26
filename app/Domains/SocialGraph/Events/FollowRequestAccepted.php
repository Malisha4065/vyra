<?php

namespace App\Domains\SocialGraph\Events;

use App\Domains\SocialGraph\Models\FollowRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FollowRequestAccepted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly FollowRequest $followRequest,
    ) {}
}
