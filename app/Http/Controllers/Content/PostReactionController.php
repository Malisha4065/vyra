<?php

namespace App\Http\Controllers\Content;

use App\Domains\Content\Actions\ReactToPostAction;
use App\Domains\Content\Actions\GetPostReactionSummaryAction;
use App\Domains\Content\Actions\RemovePostReactionAction;
use App\Domains\Content\Data\GetPostReactionSummaryData;
use App\Domains\Content\Data\ReactToPostData;
use App\Domains\Content\Data\RemovePostReactionData;
use App\Domains\Content\Models\PostReaction;
use App\Domains\Content\Repositories\PostRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class PostReactionController extends Controller
{
    public function store(
        string $post,
        ReactToPostAction $action,
        PostRepositoryInterface $postRepository,
    ): RedirectResponse {
        $data = ReactToPostData::from([
            ...request()->all(),
            'post_id' => $post,
        ]);

        $postModel = $postRepository->findById($data->post_id);

        abort_if($postModel === null, 404);

        $this->authorize('create', [PostReaction::class, $postModel]);

        /** @var \App\Domains\Identity\Models\User $user */
        $user = Auth::user();
        $action($user, $postModel, $data);

        return back()->with('success', 'Reaction saved.');
    }

    public function destroy(
        string $post,
        RemovePostReactionAction $action,
        PostRepositoryInterface $postRepository,
    ): RedirectResponse {
        $data = RemovePostReactionData::from([
            'post_id' => $post,
        ]);

        $postModel = $postRepository->findById($data->post_id);

        abort_if($postModel === null, 404);

        $this->authorize('delete', [PostReaction::class, $postModel]);

        /** @var \App\Domains\Identity\Models\User $user */
        $user = Auth::user();
        $action($user, $postModel, $data);

        return back()->with('success', 'Reaction removed.');
    }

    public function summary(
        string $post,
        GetPostReactionSummaryAction $action,
        PostRepositoryInterface $postRepository,
    ): JsonResponse {
        $data = GetPostReactionSummaryData::from([
            'post_id' => $post,
        ]);

        $postModel = $postRepository->findById($data->post_id);

        abort_if($postModel === null, 404);

        $this->authorize('view', $postModel);

        return response()->json($action($data));
    }
}
