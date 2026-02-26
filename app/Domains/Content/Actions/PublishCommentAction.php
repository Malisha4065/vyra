<?php

namespace App\Domains\Content\Actions;

use App\Domains\Content\Data\PublishCommentData;
use App\Domains\Content\Events\PostCommented;
use App\Domains\Content\Exceptions\CommentNotFoundException;
use App\Domains\Content\Exceptions\EmptyCommentException;
use App\Domains\Content\Exceptions\InvalidCommentParentException;
use App\Domains\Content\Models\Comment;
use App\Domains\Content\Models\Post;
use App\Domains\Content\Repositories\CommentRepositoryInterface;
use App\Domains\Identity\Models\User;

class PublishCommentAction
{
    public function __construct(
        private readonly CommentRepositoryInterface $commentRepository,
    ) {}

    /**
     * @throws EmptyCommentException
     * @throws CommentNotFoundException
     * @throws InvalidCommentParentException
     */
    public function __invoke(User $author, Post $post, PublishCommentData $data): Comment
    {
        $body = $data->normalizedBody();

        if ($body === '') {
            throw new EmptyCommentException();
        }

        if ($data->parent_comment_id !== null) {
            $parent = $this->commentRepository->findById($data->parent_comment_id);

            if ($parent === null) {
                throw new CommentNotFoundException();
            }

            if ($parent->post_id !== $post->id) {
                throw new InvalidCommentParentException();
            }
        }

        $comment = $this->commentRepository->create([
            'post_id' => $post->id,
            'user_id' => $author->id,
            'parent_comment_id' => $data->parent_comment_id,
            'body' => $body,
        ]);

        event(new PostCommented($comment, $post, $author));

        return $comment;
    }
}
