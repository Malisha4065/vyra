<?php

namespace App\Domains\Content\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostCommentDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $commentId,
        public readonly string $postId,
        public readonly string $authorId,
    ) {}
}
