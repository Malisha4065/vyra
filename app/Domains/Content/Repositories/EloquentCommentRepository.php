<?php

namespace App\Domains\Content\Repositories;

use App\Domains\Content\Models\Comment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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

    public function getReplies(string $commentId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->where('parent_comment_id', $commentId)
            ->with(['author.profile'])
            ->oldest()
            ->paginate($perPage);
    }
}
