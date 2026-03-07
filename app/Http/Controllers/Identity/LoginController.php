<?php

namespace App\Http\Controllers\Identity;

use App\Domains\Identity\Actions\LoginUserAction;
use App\Domains\Identity\Data\LoginData;
use App\Domains\Identity\Exceptions\InvalidCredentialsException;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function store(
        LoginData $data,
        LoginUserAction $action,
    ): RedirectResponse {
        try {
            $user = $action($data);
        } catch (InvalidCredentialsException $e) {
            return back()->withErrors([
                'email' => $e->getMessage(),
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        return redirect()->intended(route('feed'));
    }
}
