<?php

namespace App\Http\Controllers\Identity;

use App\Domains\Identity\Actions\RegisterUserAction;
use App\Domains\Identity\Data\RegisterUserData;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(
        RegisterUserData $data,
        RegisterUserAction $action,
    ): RedirectResponse {
        $user = $action($data);

        Auth::login($user);

        return redirect()->route('verification.notice')
            ->with('success', 'Account created. Verify your email to continue.');
    }
}
