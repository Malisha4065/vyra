<?php

namespace App\Domains\SocialGraph;

use App\Domains\Identity\Models\User;
use App\Domains\SocialGraph\Events\FollowRequestAccepted;
use App\Domains\SocialGraph\Events\FollowRequestReceived;
use App\Domains\SocialGraph\Events\UserBlocked;
use App\Domains\SocialGraph\Events\UserFollowed;
use App\Domains\SocialGraph\Events\UserMuted;
use App\Domains\SocialGraph\Events\UserUnfollowed;
use App\Domains\SocialGraph\Listeners\QueueFollowRequestAcceptedSideEffectsListener;
use App\Domains\SocialGraph\Listeners\QueueFollowRequestReceivedSideEffectsListener;
use App\Domains\SocialGraph\Listeners\QueueUserBlockedSideEffectsListener;
use App\Domains\SocialGraph\Listeners\QueueUserFollowedSideEffectsListener;
use App\Domains\SocialGraph\Listeners\QueueUserMutedSideEffectsListener;
use App\Domains\SocialGraph\Listeners\QueueUserUnfollowedSideEffectsListener;
use App\Domains\SocialGraph\Models\FollowRequest;
use App\Domains\SocialGraph\Policies\FollowPolicy;
use App\Domains\SocialGraph\Policies\FollowRequestPolicy;
use App\Domains\SocialGraph\Repositories\BlockRepositoryInterface;
use App\Domains\SocialGraph\Repositories\EloquentBlockRepository;
use App\Domains\SocialGraph\Repositories\EloquentFollowRepository;
use App\Domains\SocialGraph\Repositories\EloquentFollowRequestRepository;
use App\Domains\SocialGraph\Repositories\EloquentMuteRepository;
use App\Domains\SocialGraph\Repositories\FollowRepositoryInterface;
use App\Domains\SocialGraph\Repositories\FollowRequestRepositoryInterface;
use App\Domains\SocialGraph\Repositories\MuteRepositoryInterface;
use Illuminate\Support\Facades\Event;
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
        Event::listen(UserFollowed::class, QueueUserFollowedSideEffectsListener::class);
        Event::listen(UserUnfollowed::class, QueueUserUnfollowedSideEffectsListener::class);
        Event::listen(FollowRequestReceived::class, QueueFollowRequestReceivedSideEffectsListener::class);
        Event::listen(FollowRequestAccepted::class, QueueFollowRequestAcceptedSideEffectsListener::class);
        Event::listen(UserBlocked::class, QueueUserBlockedSideEffectsListener::class);
        Event::listen(UserMuted::class, QueueUserMutedSideEffectsListener::class);

        // Policy registrations
        Gate::policy(User::class, FollowPolicy::class);
        Gate::policy(FollowRequest::class, FollowRequestPolicy::class);
    }
}
