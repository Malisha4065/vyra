<?php

namespace App\Http\Controllers\SocialGraph;

use App\Domains\Identity\Repositories\UserRepositoryInterface;
use App\Domains\SocialGraph\Actions\BlockUserAction;
use App\Domains\SocialGraph\Actions\UnblockUserAction;
use App\Domains\SocialGraph\Data\BlockUserData;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class BlockController extends Controller
{
    /**
     * Block a user.
     */
    public function store(
        string $user,
        BlockUserAction $action,
        UserRepositoryInterface $userRepository,
    ): RedirectResponse {
        $data = BlockUserData::from([
            'target_user_id' => $user,
        ]);

        $targetUser = $userRepository->findById($data->target_user_id);

        abort_if($targetUser === null, 404);

        $this->authorize('block', $targetUser);

        /** @var \App\Domains\Identity\Models\User $authUser */
        $authUser = Auth::user();
        $action($authUser, $targetUser, $data);

        return back()->with('success', "You have blocked @{$targetUser->username}.");
    }

    /**
     * Unblock a user.
     */
    public function destroy(
        string $user,
        UnblockUserAction $action,
        UserRepositoryInterface $userRepository,
    ): RedirectResponse {
        $data = BlockUserData::from([
            'target_user_id' => $user,
        ]);

        $targetUser = $userRepository->findById($data->target_user_id);

        abort_if($targetUser === null, 404);

        $this->authorize('unblock', $targetUser);

        /** @var \App\Domains\Identity\Models\User $authUser */
        $authUser = Auth::user();
        $action($authUser, $targetUser, $data);

        return back()->with('success', "You have unblocked @{$targetUser->username}.");
    }
}
