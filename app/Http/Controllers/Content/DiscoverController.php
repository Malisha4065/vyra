<?php

namespace App\Http\Controllers\Content;

use App\Domains\Content\Actions\DiscoverContentAction;
use App\Domains\Content\Data\SearchContentData;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DiscoverController extends Controller
{
    public function index(Request $request, DiscoverContentAction $action): Response|JsonResponse
    {
        /** @var \App\Domains\Identity\Models\User $user */
        $user = Auth::user();

        $data = SearchContentData::from([
            ...$request->all(),
            'user_id' => $user->id,
        ]);
        $result = $action($data);

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $result['results'],
                'users' => $result['users'],
                'meta' => [
                    'query' => $data->query,
                    'hashtag' => $data->hashtag,
                ],
                'trending_hashtags' => $result['trending_hashtags'],
            ]);
        }

        return Inertia::render('Content/Discover', [
            'results' => $result['results'],
            'users' => $result['users'],
            'trendingHashtags' => $result['trending_hashtags'],
            'query' => $data->query,
            'hashtag' => $data->hashtag,
        ]);
    }
}
