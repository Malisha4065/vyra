<?php

namespace App\Domains\Content\Repositories;

use App\Domains\Content\Models\Comment;

interface CommentRepositoryInterface
{
    public function create(array $attributes): Comment;

    public function findById(string $id): ?Comment;

    public function delete(Comment $comment): void;
}
