<?php

namespace App\Http\Controllers\Content;

use App\Domains\Content\Actions\DiscoverContentAction;
use App\Domains\Content\Data\SearchContentData;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DiscoverController extends Controller
{
    public function index(Request $request, DiscoverContentAction $action): Response|JsonResponse
    {
        $data = SearchContentData::from($request->all());
        $result = $action($data);

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $result['results'],
                'meta' => [
                    'query' => $data->query,
                    'hashtag' => $data->hashtag,
                ],
                'trending_hashtags' => $result['trending_hashtags'],
            ]);
        }

        return Inertia::render('Content/Discover', [
            'results' => $result['results'],
            'trendingHashtags' => $result['trending_hashtags'],
            'query' => $data->query,
            'hashtag' => $data->hashtag,
        ]);
    }
}
