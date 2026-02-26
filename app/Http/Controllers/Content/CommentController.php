<?php

namespace App\Http\Controllers\Content;

use App\Domains\Content\Actions\DeleteCommentAction;
use App\Domains\Content\Actions\PublishCommentAction;
use App\Domains\Content\Data\DeleteCommentData;
use App\Domains\Content\Data\PublishCommentData;
use App\Domains\Content\Exceptions\CommentNotFoundException;
use App\Domains\Content\Exceptions\EmptyCommentException;
use App\Domains\Content\Exceptions\InvalidCommentParentException;
use App\Domains\Content\Models\Comment;
use App\Domains\Content\Repositories\CommentRepositoryInterface;
use App\Domains\Content\Repositories\PostRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(
        string $post,
        PublishCommentAction $action,
        PostRepositoryInterface $postRepository,
    ): RedirectResponse {
        $data = PublishCommentData::from([
            ...request()->all(),
            'post_id' => $post,
        ]);

        $postModel = $postRepository->findById($data->post_id);

        abort_if($postModel === null, 404);

        $this->authorize('create', [Comment::class, $postModel]);

        try {
            /** @var \App\Domains\Identity\Models\User $user */
            $user = Auth::user();
            $action($user, $postModel, $data);
        } catch (EmptyCommentException|CommentNotFoundException|InvalidCommentParentException $e) {
            return back()->withErrors([
                'comment' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Comment published.');
    }

    public function destroy(
        string $comment,
        DeleteCommentAction $action,
        CommentRepositoryInterface $commentRepository,
    ): RedirectResponse {
        $data = DeleteCommentData::from([
            'comment_id' => $comment,
        ]);

        $commentModel = $commentRepository->findById($data->comment_id);

        abort_if($commentModel === null, 404);

        $this->authorize('delete', $commentModel);

        $action($commentModel, $data);

        return back()->with('success', 'Comment deleted.');
    }
}
