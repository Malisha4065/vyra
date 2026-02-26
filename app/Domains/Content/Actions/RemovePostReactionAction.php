<?php

namespace App\Domains\Content\Actions;

use App\Domains\Content\Data\RemovePostReactionData;
use App\Domains\Content\Events\PostReactionRemoved;
use App\Domains\Content\Models\Post;
use App\Domains\Content\Repositories\PostReactionRepositoryInterface;
use App\Domains\Identity\Models\User;

class RemovePostReactionAction
{
    public function __construct(
        private readonly PostReactionRepositoryInterface $reactionRepository,
    ) {}

    public function __invoke(User $reactor, Post $post, RemovePostReactionData $data): void
    {
        if ($data->post_id !== $post->id) {
            return;
        }

        $reaction = $this->reactionRepository->deleteByUserAndPost($post->id, $reactor->id);

        if ($reaction === null) {
            return;
        }

        event(new PostReactionRemoved($post, $reactor, $reaction->type));
    }
}
