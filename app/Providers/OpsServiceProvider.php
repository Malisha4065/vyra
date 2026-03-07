<?php

namespace App\Providers;

use App\Domains\Identity\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class OpsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('viewHorizon', function (User $user): bool {
            if (app()->environment('local')) {
                return true;
            }

            /** @var array<int, string> $allowedEmails */
            $allowedEmails = config('horizon.allowed_emails', []);

            return in_array($user->email, $allowedEmails, true);
        });
    }
}
