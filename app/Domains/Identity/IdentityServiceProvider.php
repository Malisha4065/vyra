<?php

namespace App\Domains\Identity;

use App\Domains\Identity\Events\UserRegistered;
use App\Domains\Identity\Listeners\CreateUserProfileListener;
use App\Domains\Identity\Models\UserProfile;
use App\Domains\Identity\Policies\UserProfilePolicy;
use App\Domains\Identity\Repositories\EloquentUserProfileRepository;
use App\Domains\Identity\Repositories\EloquentUserRepository;
use App\Domains\Identity\Repositories\UserProfileRepositoryInterface;
use App\Domains\Identity\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(UserProfileRepositoryInterface::class, EloquentUserProfileRepository::class);
    }

    public function boot(): void
    {
        // Event → Listener mappings
        Event::listen(UserRegistered::class, CreateUserProfileListener::class);

        // Policy registrations
        Gate::policy(UserProfile::class, UserProfilePolicy::class);
    }
}
