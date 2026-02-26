<?php

namespace App\Domains\Content\Actions;

use App\Domains\Content\Data\DeleteCommentData;
use App\Domains\Content\Events\PostCommentDeleted;
use App\Domains\Content\Models\Comment;
use App\Domains\Content\Repositories\CommentRepositoryInterface;

class DeleteCommentAction
{
    public function __construct(
        private readonly CommentRepositoryInterface $commentRepository,
    ) {}

    public function __invoke(Comment $comment, DeleteCommentData $data): void
    {
        if ($data->comment_id !== $comment->id) {
            return;
        }

        $payload = [
            'comment_id' => $comment->id,
            'post_id' => $comment->post_id,
            'author_id' => $comment->user_id,
        ];

        $this->commentRepository->delete($comment);

        event(new PostCommentDeleted(
            commentId: $payload['comment_id'],
            postId: $payload['post_id'],
            authorId: $payload['author_id'],
        ));
    }
}
