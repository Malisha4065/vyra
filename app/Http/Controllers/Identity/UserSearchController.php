<?php

namespace App\Http\Controllers\Identity;

use App\Domains\Identity\Actions\SearchUsersAction;
use App\Domains\Identity\Data\SearchUsersData;
use App\Domains\Identity\Models\UserProfile;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserSearchController extends Controller
{
    public function index(SearchUsersAction $action): JsonResponse
    {
        $this->authorize('viewAny', UserProfile::class);

        /** @var \App\Domains\Identity\Models\User $user */
        $user = Auth::user();

        $data = SearchUsersData::from([
            ...request()->all(),
            'viewer_id' => $user->id,
            'query' => request()->string('query')->toString(),
        ]);

        return response()->json([
            'data' => $action($data),
            'meta' => [
                'query' => $data->query,
                'limit' => $data->limit,
            ],
        ]);
    }
}
