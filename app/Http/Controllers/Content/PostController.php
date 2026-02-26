<?php

namespace App\Http\Controllers\Content;

use App\Domains\Content\Actions\PublishPostAction;
use App\Domains\Content\Actions\DeletePostAction;
use App\Domains\Content\Actions\UpdatePostAction;
use App\Domains\Content\Data\DeletePostData;
use App\Domains\Content\Data\PublishPostData;
use App\Domains\Content\Data\UpdatePostData;
use App\Domains\Content\Exceptions\EmptyPostException;
use App\Domains\Content\Models\Post;
use App\Domains\Content\Repositories\PostRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function store(
        PublishPostData $data,
        PublishPostAction $action,
    ): RedirectResponse {
        $this->authorize('create', Post::class);

        try {
            /** @var \App\Domains\Identity\Models\User $user */
            $user = Auth::user();
            $action($user, $data);
        } catch (EmptyPostException $e) {
            return back()->withErrors([
                'post' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Post published.');
    }

    public function update(
        string $post,
        UpdatePostAction $action,
        PostRepositoryInterface $postRepository,
    ): RedirectResponse {
        $data = UpdatePostData::from([
            ...request()->all(),
            'post_id' => $post,
        ]);

        $postModel = $postRepository->findById($data->post_id);

        abort_if($postModel === null, 404);

        $this->authorize('update', $postModel);

        try {
            /** @var \App\Domains\Identity\Models\User $user */
            $user = Auth::user();
            $action($user, $postModel, $data);
        } catch (EmptyPostException $e) {
            return back()->withErrors([
                'post' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Post updated.');
    }

    public function destroy(
        string $post,
        DeletePostAction $action,
        PostRepositoryInterface $postRepository,
    ): RedirectResponse {
        $data = DeletePostData::from([
            'post_id' => $post,
        ]);

        $postModel = $postRepository->findById($data->post_id);

        abort_if($postModel === null, 404);

        $this->authorize('delete', $postModel);

        $action($postModel, $data);

        return back()->with('success', 'Post deleted.');
    }
}
