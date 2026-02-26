<?php

namespace App\Http\Controllers\SocialGraph;

use App\Domains\Identity\Repositories\UserRepositoryInterface;
use App\Domains\SocialGraph\Actions\MuteUserAction;
use App\Domains\SocialGraph\Actions\UnmuteUserAction;
use App\Domains\SocialGraph\Data\MuteUserData;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class MuteController extends Controller
{
    /**
     * Mute a user.
     */
    public function store(
        string $user,
        MuteUserAction $action,
        UserRepositoryInterface $userRepository,
    ): RedirectResponse {
        $data = MuteUserData::from([
            'target_user_id' => $user,
        ]);

        $targetUser = $userRepository->findById($data->target_user_id);

        abort_if($targetUser === null, 404);

        $this->authorize('mute', $targetUser);

        /** @var \App\Domains\Identity\Models\User $authUser */
        $authUser = Auth::user();
        $action($authUser, $targetUser, $data);

        return back()->with('success', "You have muted @{$targetUser->username}.");
    }

    /**
     * Unmute a user.
     */
    public function destroy(
        string $user,
        UnmuteUserAction $action,
        UserRepositoryInterface $userRepository,
    ): RedirectResponse {
        $data = MuteUserData::from([
            'target_user_id' => $user,
        ]);

        $targetUser = $userRepository->findById($data->target_user_id);

        abort_if($targetUser === null, 404);

        $this->authorize('unmute', $targetUser);

        /** @var \App\Domains\Identity\Models\User $authUser */
        $authUser = Auth::user();
        $action($authUser, $targetUser, $data);

        return back()->with('success', "You have unmuted @{$targetUser->username}.");
    }
}
