<?php

namespace App\Http\Controllers\Identity;

use App\Domains\Identity\Actions\RequestEmailVerificationAction;
use App\Domains\Identity\Actions\VerifyEmailAction;
use App\Domains\Identity\Exceptions\InvalidEmailVerificationLinkException;
use App\Domains\Identity\Repositories\UserRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationController extends Controller
{
    public function notice(): Response|RedirectResponse
    {
        /** @var \App\Domains\Identity\Models\User $user */
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('feed');
        }

        return Inertia::render('Auth/VerifyEmail');
    }

    public function verify(
        string $id,
        string $hash,
        VerifyEmailAction $action,
        UserRepositoryInterface $userRepository,
    ): RedirectResponse {
        $user = $userRepository->findById($id);

        abort_if($user === null, 404);

        /** @var \App\Domains\Identity\Models\User $authUser */
        $authUser = Auth::user();
        abort_unless($authUser->id === $user->id, 403);

        try {
            $wasVerified = $action($user, $hash);
        } catch (InvalidEmailVerificationLinkException) {
            abort(403);
        }

        return redirect()->route('feed')->with(
            'success',
            $wasVerified ? 'Email verified.' : 'Your email is already verified.',
        );
    }

    public function resend(RequestEmailVerificationAction $action): RedirectResponse
    {
        /** @var \App\Domains\Identity\Models\User $user */
        $user = Auth::user();

        $action($user);

        return back()->with('success', 'Verification email sent.');
    }
}
