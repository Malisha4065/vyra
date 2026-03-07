<?php

namespace App\Http\Controllers\Identity;

use App\Domains\Identity\Actions\UpdatePrivacySettingsAction;
use App\Domains\Identity\Actions\UpdateProfileAction;
use App\Domains\Identity\Actions\UpdatePasswordAction;
use App\Domains\Identity\Actions\DeleteAccountAction;
use App\Domains\Identity\Data\DeleteAccountData;
use App\Domains\Identity\Data\UpdatePasswordData;
use App\Domains\Identity\Data\UpdatePrivacySettingsData;
use App\Domains\Identity\Data\UpdateProfileData;
use App\Domains\Identity\Data\UserData;
use App\Domains\Identity\Data\UserProfileData;
use App\Domains\Identity\Models\UserProfile;
use App\Domains\Identity\Repositories\UserRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * Display a user's public profile.
     */
    public function show(string $username): Response
    {
        $user = $this->userRepository->findByUsername($username);

        abort_if($user === null, 404);

        $profile = $user->profile;

        $this->authorize('view', $profile);

        return Inertia::render('Profile/Show', [
            'user' => UserData::fromModel($user),
            'profile' => UserProfileData::fromModel($profile),
        ]);
    }

    /**
     * Show the edit form for the authenticated user's profile.
     */
    public function edit(): Response
    {
        /** @var \App\Domains\Identity\Models\User $user */
        $user = Auth::user();
        $profile = $user->profile;

        $this->authorize('update', $profile);

        return Inertia::render('Profile/Edit', [
            'user' => UserData::fromModel($user),
            'profile' => UserProfileData::fromModel($profile),
        ]);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(
        UpdateProfileData $data,
        UpdateProfileAction $action,
    ): RedirectResponse {
        /** @var \App\Domains\Identity\Models\User $user */
        $user = Auth::user();

        $this->authorize('update', $user->profile);

        $action($user, $data);

        return back()->with('success', 'Profile updated.');
    }

    /**
     * Update privacy settings.
     */
    public function updatePrivacy(
        UpdatePrivacySettingsData $data,
        UpdatePrivacySettingsAction $action,
    ): RedirectResponse {
        /** @var \App\Domains\Identity\Models\User $user */
        $user = Auth::user();

        $this->authorize('update', $user->profile);

        $action($user, $data);

        return back()->with('success', 'Privacy settings updated.');
    }

    public function updatePassword(
        UpdatePasswordData $data,
        UpdatePasswordAction $action,
    ): RedirectResponse {
        /** @var \App\Domains\Identity\Models\User $user */
        $user = Auth::user();

        $this->authorize('update', $user->profile);

        $action($user, $data);

        return back()->with('success', 'Password updated.');
    }

    public function destroy(
        Request $request,
        DeleteAccountData $data,
        DeleteAccountAction $action,
    ): RedirectResponse {
        /** @var \App\Domains\Identity\Models\User $user */
        $user = Auth::user();

        $this->authorize('update', $user->profile);

        $action($user, $data);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Account deleted.');
    }
}
