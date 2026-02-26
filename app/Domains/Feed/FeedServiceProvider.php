<?php

namespace App\Domains\Feed;

use App\Domains\Feed\Models\UserFeed;
use App\Domains\Feed\Policies\FeedPolicy;
use App\Domains\Feed\Repositories\FeedCacheRepositoryInterface;
use App\Domains\Feed\Repositories\HybridFeedRepositoryInterface;
use App\Domains\Feed\Repositories\RedisFeedCacheRepository;
use App\Domains\Feed\Repositories\RedisHybridFeedRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class FeedServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(FeedCacheRepositoryInterface::class, RedisFeedCacheRepository::class);
        $this->app->bind(HybridFeedRepositoryInterface::class, RedisHybridFeedRepository::class);
    }

    public function boot(): void
    {
        Gate::policy(UserFeed::class, FeedPolicy::class);
    }
}
