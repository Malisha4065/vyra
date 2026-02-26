<?php

namespace App\Domains\Identity\Events;

use App\Domains\Identity\Models\UserProfile;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProfileUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly UserProfile $profile,
    ) {}
}
