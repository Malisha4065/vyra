<?php

namespace App\Domains\Identity;

use App\Domains\Identity\Events\AccountDeleted;
use App\Domains\Identity\Events\EmailVerificationRequested;
use App\Domains\Identity\Events\PasswordResetLinkRequested;
use App\Domains\Identity\Events\ProfileUpdated;
use App\Domains\Identity\Events\UserRegistered;
use App\Domains\Identity\Listeners\CreateUserProfileListener;
use App\Domains\Identity\Listeners\QueueRemoveDeletedUserSearchIndexListener;
use App\Domains\Identity\Listeners\QueueSendEmailVerificationListener;
use App\Domains\Identity\Listeners\QueueSendPasswordResetLinkListener;
use App\Domains\Identity\Listeners\QueueUserSearchIndexListener;
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
        Event::listen(UserRegistered::class, QueueSendEmailVerificationListener::class);
        Event::listen(EmailVerificationRequested::class, QueueSendEmailVerificationListener::class);
        Event::listen(PasswordResetLinkRequested::class, QueueSendPasswordResetLinkListener::class);
        Event::listen(ProfileUpdated::class, QueueUserSearchIndexListener::class);
        Event::listen(AccountDeleted::class, QueueRemoveDeletedUserSearchIndexListener::class);

        // Policy registrations
        Gate::policy(UserProfile::class, UserProfilePolicy::class);
    }
}
