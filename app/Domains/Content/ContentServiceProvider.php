<?php

namespace App\Domains\Content;

use App\Domains\Content\Events\PostCommented;
use App\Domains\Content\Events\PostCommentDeleted;
use App\Domains\Content\Events\PostReactionAdded;
use App\Domains\Content\Events\PostReactionRemoved;
use App\Domains\Content\Events\PostPublished;
use App\Domains\Content\Listeners\QueuePostCommentedSideEffectsListener;
use App\Domains\Content\Listeners\QueuePostCommentDeletedSideEffectsListener;
use App\Domains\Content\Listeners\QueuePostFanOutListener;
use App\Domains\Content\Listeners\QueuePostMediaProcessingListener;
use App\Domains\Content\Listeners\QueuePostReactionAddedSideEffectsListener;
use App\Domains\Content\Listeners\QueuePostReactionRemovedSideEffectsListener;
use App\Domains\Content\Listeners\QueuePostSearchIndexListener;
use App\Domains\Content\Models\Comment;
use App\Domains\Content\Models\Post;
use App\Domains\Content\Models\PostReaction;
use App\Domains\Content\Policies\CommentPolicy;
use App\Domains\Content\Policies\PostPolicy;
use App\Domains\Content\Policies\PostReactionPolicy;
use App\Domains\Content\Repositories\CommentRepositoryInterface;
use App\Domains\Content\Repositories\EloquentCommentRepository;
use App\Domains\Content\Repositories\EloquentPostRepository;
use App\Domains\Content\Repositories\EloquentPostReactionRepository;
use App\Domains\Content\Repositories\PostRepositoryInterface;
use App\Domains\Content\Repositories\PostReactionRepositoryInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class ContentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PostRepositoryInterface::class, EloquentPostRepository::class);
        $this->app->bind(CommentRepositoryInterface::class, EloquentCommentRepository::class);
        $this->app->bind(PostReactionRepositoryInterface::class, EloquentPostReactionRepository::class);
    }

    public function boot(): void
    {
        Event::listen(PostPublished::class, QueuePostMediaProcessingListener::class);
        Event::listen(PostPublished::class, QueuePostSearchIndexListener::class);
        Event::listen(PostPublished::class, QueuePostFanOutListener::class);
        Event::listen(PostCommented::class, QueuePostCommentedSideEffectsListener::class);
        Event::listen(PostCommentDeleted::class, QueuePostCommentDeletedSideEffectsListener::class);
        Event::listen(PostReactionAdded::class, QueuePostReactionAddedSideEffectsListener::class);
        Event::listen(PostReactionRemoved::class, QueuePostReactionRemovedSideEffectsListener::class);

        Gate::policy(Post::class, PostPolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);
        Gate::policy(PostReaction::class, PostReactionPolicy::class);
    }
}
