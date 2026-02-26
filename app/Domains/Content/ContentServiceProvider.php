<?php

namespace App\Domains\Content;

use App\Domains\Content\Events\PostPublished;
use App\Domains\Content\Listeners\QueuePostFanOutListener;
use App\Domains\Content\Listeners\QueuePostMediaProcessingListener;
use App\Domains\Content\Listeners\QueuePostSearchIndexListener;
use App\Domains\Content\Models\Post;
use App\Domains\Content\Policies\PostPolicy;
use App\Domains\Content\Repositories\EloquentPostRepository;
use App\Domains\Content\Repositories\PostRepositoryInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class ContentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PostRepositoryInterface::class, EloquentPostRepository::class);
    }

    public function boot(): void
    {
        Event::listen(PostPublished::class, QueuePostMediaProcessingListener::class);
        Event::listen(PostPublished::class, QueuePostSearchIndexListener::class);
        Event::listen(PostPublished::class, QueuePostFanOutListener::class);

        Gate::policy(Post::class, PostPolicy::class);
    }
}
