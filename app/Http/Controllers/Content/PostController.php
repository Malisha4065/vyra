<?php

namespace App\Http\Controllers\Content;

use App\Domains\Content\Actions\PublishPostAction;
use App\Domains\Content\Data\PublishPostData;
use App\Domains\Content\Exceptions\EmptyPostException;
use App\Domains\Content\Models\Post;
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
}
