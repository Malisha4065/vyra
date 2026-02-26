<?php

namespace App\Domains\Content\Events;

use App\Domains\Content\Models\Comment;
use App\Domains\Content\Models\Post;
use App\Domains\Identity\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostCommented
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Comment $comment,
        public readonly Post $post,
        public readonly User $author,
    ) {}
}
