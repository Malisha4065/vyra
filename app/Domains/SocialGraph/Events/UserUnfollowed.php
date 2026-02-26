<?php

namespace App\Domains\SocialGraph\Events;

use App\Domains\Identity\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserUnfollowed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly User $follower,
        public readonly User $followee,
    ) {}
}
