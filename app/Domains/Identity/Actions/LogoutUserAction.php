<?php

namespace App\Domains\Identity\Actions;

use Illuminate\Support\Facades\Auth;

class LogoutUserAction
{
    public function __invoke(): void
    {
        Auth::guard('web')->logout();

        session()->invalidate();
        session()->regenerateToken();
    }
}
