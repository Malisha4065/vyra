<?php

namespace App\Http\Controllers\SocialGraph;

use App\Domains\Identity\Models\User;
use App\Domains\SocialGraph\Actions\MuteUserAction;
use App\Domains\SocialGraph\Actions\UnmuteUserAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class MuteController extends Controller
{
    /**
     * Mute a user.
     */
    public function store(
        User $user,
        MuteUserAction $action,
    ): RedirectResponse {
        $this->authorize('mute', $user);

        /** @var \App\Domains\Identity\Models\User $authUser */
        $authUser = Auth::user();
        $action($authUser, $user);

        return back()->with('success', "You have muted @{$user->username}.");
    }

    /**
     * Unmute a user.
     */
    public function destroy(
        User $user,
        UnmuteUserAction $action,
    ): RedirectResponse {
        /** @var \App\Domains\Identity\Models\User $authUser */
        $authUser = Auth::user();
        $action($authUser, $user);

        return back()->with('success', "You have unmuted @{$user->username}.");
    }
}
