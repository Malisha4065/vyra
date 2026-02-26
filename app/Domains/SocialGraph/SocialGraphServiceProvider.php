<?php

namespace App\Domains\SocialGraph;

use App\Domains\Identity\Models\User;
use App\Domains\SocialGraph\Policies\FollowPolicy;
use App\Domains\SocialGraph\Repositories\BlockRepositoryInterface;
use App\Domains\SocialGraph\Repositories\EloquentBlockRepository;
use App\Domains\SocialGraph\Repositories\EloquentFollowRepository;
use App\Domains\SocialGraph\Repositories\EloquentFollowRequestRepository;
use App\Domains\SocialGraph\Repositories\EloquentMuteRepository;
use App\Domains\SocialGraph\Repositories\FollowRepositoryInterface;
use App\Domains\SocialGraph\Repositories\FollowRequestRepositoryInterface;
use App\Domains\SocialGraph\Repositories\MuteRepositoryInterface;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class SocialGraphServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(FollowRepositoryInterface::class, EloquentFollowRepository::class);
        $this->app->bind(FollowRequestRepositoryInterface::class, EloquentFollowRequestRepository::class);
        $this->app->bind(BlockRepositoryInterface::class, EloquentBlockRepository::class);
        $this->app->bind(MuteRepositoryInterface::class, EloquentMuteRepository::class);
    }

    public function boot(): void
    {
        // Policy registrations
        Gate::policy(User::class, FollowPolicy::class);
    }
}
