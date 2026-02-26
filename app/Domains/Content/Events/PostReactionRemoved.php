<?php

namespace App\Domains\Content\Events;

use App\Domains\Content\Models\Post;
use App\Domains\Identity\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostReactionRemoved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Post $post,
        public readonly User $reactor,
        public readonly string $type,
    ) {}
}
