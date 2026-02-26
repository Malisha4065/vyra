<?php

use App\Domains\Communication\Events\ConversationMessagesRead;
use App\Domains\Communication\Events\ConversationTypingUpdated;
use App\Domains\Communication\Events\MessageSent;
use App\Domains\Communication\Jobs\BroadcastConversationReadJob;
use App\Domains\Communication\Jobs\BroadcastMessageSentJob;
use App\Domains\Communication\Jobs\BroadcastTypingIndicatorJob;
use App\Domains\Communication\Listeners\QueueBroadcastConversationReadListener;
use App\Domains\Communication\Listeners\QueueBroadcastMessageSentListener;
use App\Domains\Communication\Listeners\QueueBroadcastTypingIndicatorListener;
use App\Domains\Communication\Models\Conversation;
use App\Domains\Communication\Models\Message;
use Illuminate\Support\Facades\Bus;

it('dispatches message-sent broadcast job to communication queue', function () {
    Bus::fake();

    $conversation = new Conversation();
    $conversation->forceFill(['id' => 'conversation-1']);

    $message = new Message();
    $message->forceFill(['id' => 'message-1']);

    $listener = new QueueBroadcastMessageSentListener();
    $listener->handle(new MessageSent($conversation, $message));

    Bus::assertDispatched(BroadcastMessageSentJob::class, function (BroadcastMessageSentJob $job): bool {
        return $job->messageId === 'message-1' && $job->queue === 'communication';
    });
});

it('dispatches conversation-read broadcast job to communication queue', function () {
    Bus::fake();

    $listener = new QueueBroadcastConversationReadListener();
    $listener->handle(new ConversationMessagesRead('conversation-1', 'user-1', ['message-1', 'message-2']));

    Bus::assertDispatched(BroadcastConversationReadJob::class, function (BroadcastConversationReadJob $job): bool {
        return $job->conversationId === 'conversation-1'
            && $job->readerId === 'user-1'
            && $job->messageIds === ['message-1', 'message-2']
            && $job->queue === 'communication';
    });
});

it('dispatches typing-indicator broadcast job to communication queue', function () {
    Bus::fake();

    $listener = new QueueBroadcastTypingIndicatorListener();
    $listener->handle(new ConversationTypingUpdated('conversation-1', 'user-1', true, '2026-02-26T00:00:00+00:00'));

    Bus::assertDispatched(BroadcastTypingIndicatorJob::class, function (BroadcastTypingIndicatorJob $job): bool {
        return $job->conversationId === 'conversation-1'
            && $job->userId === 'user-1'
            && $job->isTyping === true
            && $job->queue === 'communication';
    });
});
