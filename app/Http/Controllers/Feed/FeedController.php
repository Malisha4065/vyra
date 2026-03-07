<?php

namespace App\Http\Controllers\Feed;

use App\Domains\Feed\Actions\GetUserFeedAction;
use App\Domains\Feed\Data\GetUserFeedData;
use App\Domains\Feed\Models\UserFeed;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class FeedController extends Controller
{
    public function index(Request $request, GetUserFeedAction $action): Response|JsonResponse
    {
        /** @var \App\Domains\Identity\Models\User $user */
        $user = Auth::user();

        $data = GetUserFeedData::from([
            ...request()->all(),
            'user_id' => $user->id,
        ]);

        $this->authorize('view', [UserFeed::class, $data->user_id]);

        $feed = $action($data);

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $feed->items,
                'meta' => [
                    'next_cursor' => $feed->next_cursor,
                ],
            ]);
        }

        return Inertia::render('Feed/Index', [
            'feed' => $feed->toArray(),
        ]);
    }
}
