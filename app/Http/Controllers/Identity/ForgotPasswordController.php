<?php

namespace App\Http\Controllers\Identity;

use App\Domains\Identity\Actions\RequestPasswordResetLinkAction;
use App\Domains\Identity\Data\RequestPasswordResetLinkData;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ForgotPasswordController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function store(
        RequestPasswordResetLinkData $data,
        RequestPasswordResetLinkAction $action,
    ): RedirectResponse {
        $action($data);

        return back()->with('success', 'If that account exists, a password reset link has been sent.');
    }
}
