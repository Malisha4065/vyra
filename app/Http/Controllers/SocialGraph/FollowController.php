<?php

namespace App\Http\Controllers\SocialGraph;

use App\Domains\Identity\Models\User;
use App\Domains\SocialGraph\Actions\FollowUserAction;
use App\Domains\SocialGraph\Actions\UnfollowUserAction;
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
        User $user,
        FollowUserAction $action,
    ): RedirectResponse {
        $this->authorize('follow', $user);

        try {
            /** @var \App\Domains\Identity\Models\User $authUser */
            $authUser = Auth::user();
            $result = $action($authUser, $user);

            $message = $result === 'followed'
                ? "You are now following @{$user->username}."
                : "Follow request sent to @{$user->username}.";

            return back()->with('success', $message);
        } catch (CannotFollowSelfException|AlreadyFollowingException|UserBlockedException $e) {
            return back()->withErrors(['follow' => $e->getMessage()]);
        }
    }

    /**
     * Unfollow a user.
     */
    public function destroy(
        User $user,
        UnfollowUserAction $action,
    ): RedirectResponse {
        $this->authorize('unfollow', $user);

        /** @var \App\Domains\Identity\Models\User $authUser */
        $authUser = Auth::user();
        $action($authUser, $user);

        return back()->with('success', "You have unfollowed @{$user->username}.");
    }
}
