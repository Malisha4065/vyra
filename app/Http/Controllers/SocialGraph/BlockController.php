<?php

namespace App\Http\Controllers\SocialGraph;

use App\Domains\Identity\Models\User;
use App\Domains\SocialGraph\Actions\BlockUserAction;
use App\Domains\SocialGraph\Actions\UnblockUserAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class BlockController extends Controller
{
    /**
     * Block a user.
     */
    public function store(
        User $user,
        BlockUserAction $action,
    ): RedirectResponse {
        $this->authorize('block', $user);

        /** @var \App\Domains\Identity\Models\User $authUser */
        $authUser = Auth::user();
        $action($authUser, $user);

        return back()->with('success', "You have blocked @{$user->username}.");
    }

    /**
     * Unblock a user.
     */
    public function destroy(
        User $user,
        UnblockUserAction $action,
    ): RedirectResponse {
        /** @var \App\Domains\Identity\Models\User $authUser */
        $authUser = Auth::user();
        $action($authUser, $user);

        return back()->with('success', "You have unblocked @{$user->username}.");
    }
}
