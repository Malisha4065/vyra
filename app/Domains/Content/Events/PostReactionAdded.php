<?php

namespace App\Domains\Content\Events;

use App\Domains\Content\Models\Post;
use App\Domains\Content\Models\PostReaction;
use App\Domains\Identity\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostReactionAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly PostReaction $reaction,
        public readonly Post $post,
        public readonly User $reactor,
    ) {}
}
