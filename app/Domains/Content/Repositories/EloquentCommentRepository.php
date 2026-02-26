<?php

namespace App\Domains\Content\Repositories;

use App\Domains\Content\Models\Comment;

class EloquentCommentRepository implements CommentRepositoryInterface
{
    public function __construct(
        private readonly Comment $model,
    ) {}

    public function create(array $attributes): Comment
    {
        return $this->model->create($attributes);
    }

    public function findById(string $id): ?Comment
    {
        return $this->model
            ->with(['author.profile', 'post'])
            ->find($id);
    }

    public function delete(Comment $comment): void
    {
        $comment->delete();
    }
}
