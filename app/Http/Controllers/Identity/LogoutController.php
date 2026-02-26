<?php

namespace App\Http\Controllers\Identity;

use App\Domains\Identity\Actions\LogoutUserAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class LogoutController extends Controller
{
    public function destroy(LogoutUserAction $action): RedirectResponse
    {
        $action();

        return redirect()->route('login');
    }
}
