<?php

namespace App\Domains\Content\Actions;

use App\Domains\Content\Data\ReactToPostData;
use App\Domains\Content\Events\PostReactionAdded;
use App\Domains\Content\Models\Post;
use App\Domains\Content\Models\PostReaction;
use App\Domains\Content\Repositories\PostReactionRepositoryInterface;
use App\Domains\Identity\Models\User;

class ReactToPostAction
{
    public function __construct(
        private readonly PostReactionRepositoryInterface $reactionRepository,
    ) {}

    public function __invoke(User $reactor, Post $post, ReactToPostData $data): PostReaction
    {
        $reaction = $this->reactionRepository->upsert(
            postId: $post->id,
            userId: $reactor->id,
            type: $data->type,
        );

        event(new PostReactionAdded($reaction, $post, $reactor));

        return $reaction;
    }
}
