<?php

namespace App\Http\Controllers\SocialGraph;

use App\Domains\Identity\Repositories\UserRepositoryInterface;
use App\Domains\SocialGraph\Actions\FollowUserAction;
use App\Domains\SocialGraph\Actions\UnfollowUserAction;
use App\Domains\SocialGraph\Data\FollowUserData;
use App\Domains\SocialGraph\Exceptions\AlreadyFollowingException;
use App\Domains\SocialGraph\Exceptions\CannotFollowSelfException;
use App\Domains\SocialGraph\Exceptions\UserBlockedException;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    /**
     * Follow a user.
     */
    public function store(
        string $user,
        FollowUserAction $action,
        UserRepositoryInterface $userRepository,
    ): RedirectResponse {
        $data = FollowUserData::from([
            'target_user_id' => $user,
        ]);

        $targetUser = $userRepository->findById($data->target_user_id);

        abort_if($targetUser === null, 404);

        $this->authorize('follow', $targetUser);

        try {
            /** @var \App\Domains\Identity\Models\User $authUser */
            $authUser = Auth::user();
            $result = $action($authUser, $targetUser, $data);

            $message = $result === 'followed'
                ? "You are now following @{$targetUser->username}."
                : "Follow request sent to @{$targetUser->username}.";

            return back()->with('success', $message);
        } catch (CannotFollowSelfException|AlreadyFollowingException|UserBlockedException $e) {
            return back()->withErrors(['follow' => $e->getMessage()]);
        }
    }

    /**
     * Unfollow a user.
     */
    public function destroy(
        string $user,
        UnfollowUserAction $action,
        UserRepositoryInterface $userRepository,
    ): RedirectResponse {
        $data = FollowUserData::from([
            'target_user_id' => $user,
        ]);

        $targetUser = $userRepository->findById($data->target_user_id);

        abort_if($targetUser === null, 404);

        $this->authorize('unfollow', $targetUser);

        /** @var \App\Domains\Identity\Models\User $authUser */
        $authUser = Auth::user();
        $action($authUser, $targetUser, $data);

        return back()->with('success', "You have unfollowed @{$targetUser->username}.");
    }
}
