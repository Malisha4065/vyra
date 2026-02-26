<?php

namespace App\Domains\Content\Repositories;

use App\Domains\Content\Models\Comment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CommentRepositoryInterface
{
    public function create(array $attributes): Comment;

    public function findById(string $id): ?Comment;

    public function delete(Comment $comment): void;

    public function getReplies(string $commentId, int $perPage = 20): LengthAwarePaginator;
}
