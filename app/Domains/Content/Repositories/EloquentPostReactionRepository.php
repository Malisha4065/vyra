<?php

namespace App\Domains\Content\Repositories;

use App\Domains\Content\Models\PostReaction;

class EloquentPostReactionRepository implements PostReactionRepositoryInterface
{
    public function __construct(
        private readonly PostReaction $model,
    ) {}

    public function upsert(string $postId, string $userId, string $type): PostReaction
    {
        return $this->model->updateOrCreate(
            [
                'post_id' => $postId,
                'user_id' => $userId,
            ],
            [
                'type' => $type,
            ],
        );
    }

    public function findByUserAndPost(string $postId, string $userId): ?PostReaction
    {
        return $this->model
            ->where('post_id', $postId)
            ->where('user_id', $userId)
            ->first();
    }

    public function deleteByUserAndPost(string $postId, string $userId): ?PostReaction
    {
        $reaction = $this->findByUserAndPost($postId, $userId);

        if ($reaction === null) {
            return null;
        }

        $reaction->delete();

        return $reaction;
    }
}
