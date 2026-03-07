<?php

namespace App\Http\Controllers\Identity;

use App\Domains\Identity\Actions\ResetPasswordAction;
use App\Domains\Identity\Data\ResetPasswordData;
use App\Domains\Identity\Exceptions\InvalidPasswordResetTokenException;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ResetPasswordController extends Controller
{
    public function create(Request $request, string $token): Response
    {
        return Inertia::render('Auth/ResetPassword', [
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function store(
        ResetPasswordData $data,
        ResetPasswordAction $action,
    ): RedirectResponse {
        try {
            $action($data);
        } catch (InvalidPasswordResetTokenException $e) {
            return back()->withErrors([
                'email' => $e->getMessage(),
            ]);
        }

        return redirect()->route('login')->with('success', 'Password reset. You can now sign in.');
    }
}
