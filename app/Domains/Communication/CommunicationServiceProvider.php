<?php

namespace App\Domains\Communication;

use App\Domains\Communication\Events\ConversationMessagesRead;
use App\Domains\Communication\Events\ConversationTypingUpdated;
use App\Domains\Communication\Events\MessageSent;
use App\Domains\Communication\Listeners\QueueBroadcastConversationReadListener;
use App\Domains\Communication\Listeners\QueueBroadcastMessageSentListener;
use App\Domains\Communication\Listeners\QueueBroadcastTypingIndicatorListener;
use App\Domains\Communication\Models\Conversation;
use App\Domains\Communication\Policies\ConversationPolicy;
use App\Domains\Communication\Repositories\ConversationRepositoryInterface;
use App\Domains\Communication\Repositories\EloquentConversationRepository;
use App\Domains\Communication\Repositories\EloquentMessageReadReceiptRepository;
use App\Domains\Communication\Repositories\EloquentMessageRepository;
use App\Domains\Communication\Repositories\MessageReadReceiptRepositoryInterface;
use App\Domains\Communication\Repositories\MessageRepositoryInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class CommunicationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ConversationRepositoryInterface::class, EloquentConversationRepository::class);
        $this->app->bind(MessageRepositoryInterface::class, EloquentMessageRepository::class);
        $this->app->bind(MessageReadReceiptRepositoryInterface::class, EloquentMessageReadReceiptRepository::class);
    }

    public function boot(): void
    {
        Event::listen(MessageSent::class, QueueBroadcastMessageSentListener::class);
        Event::listen(ConversationMessagesRead::class, QueueBroadcastConversationReadListener::class);
        Event::listen(ConversationTypingUpdated::class, QueueBroadcastTypingIndicatorListener::class);

        Gate::policy(Conversation::class, ConversationPolicy::class);
    }
}
