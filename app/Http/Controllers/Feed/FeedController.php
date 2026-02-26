<?php

namespace App\Http\Controllers\Feed;

use App\Domains\Feed\Actions\GetUserFeedAction;
use App\Domains\Feed\Data\GetUserFeedData;
use App\Domains\Feed\Models\UserFeed;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class FeedController extends Controller
{
    public function index(GetUserFeedAction $action): Response
    {
        /** @var \App\Domains\Identity\Models\User $user */
        $user = Auth::user();

        $data = GetUserFeedData::from([
            ...request()->all(),
            'user_id' => $user->id,
        ]);

        $this->authorize('view', [UserFeed::class, $data->user_id]);

        return Inertia::render('Feed/Index', [
            'feed' => $action($data),
        ]);
    }
}
