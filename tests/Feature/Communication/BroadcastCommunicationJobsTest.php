<?php

use App\Domains\Communication\Events\Broadcast\ConversationReadBroadcast;
use App\Domains\Communication\Events\Broadcast\MessageSentBroadcast;
use App\Domains\Communication\Events\Broadcast\TypingIndicatorBroadcast;
use App\Domains\Communication\Jobs\BroadcastConversationReadJob;
use App\Domains\Communication\Jobs\BroadcastMessageSentJob;
use App\Domains\Communication\Jobs\BroadcastTypingIndicatorJob;
use App\Domains\Communication\Models\Message;
use App\Domains\Communication\Repositories\MessageRepositoryInterface;
use App\Domains\Identity\Models\User;
use Illuminate\Support\Facades\Event;

it('broadcasts message-sent payload for existing message', function () {
    Event::fake();

    $sender = new User();
    $sender->forceFill([
        'id' => 'user-1',
        'username' => 'alice',
    ]);

    $message = new Message();
    $message->forceFill([
        'id' => 'message-1',
        'conversation_id' => 'conversation-1',
        'sender_id' => 'user-1',
        'body' => 'hello',
        'metadata' => ['client_id' => 'abc'],
        'created_at' => now(),
    ]);
    $message->setRelation('sender', $sender);

    $messageRepository = mock(MessageRepositoryInterface::class);
    $messageRepository->shouldReceive('findById')->once()->with('message-1')->andReturn($message);

    $job = new BroadcastMessageSentJob('message-1');
    $job->handle($messageRepository);

    Event::assertDispatched(MessageSentBroadcast::class, function (MessageSentBroadcast $event): bool {
        return $event->conversationId === 'conversation-1'
            && $event->message['id'] === 'message-1'
            && $event->message['sender']['username'] === 'alice';
    });
});

it('does not broadcast message payload when message is missing', function () {
    Event::fake();

    $messageRepository = mock(MessageRepositoryInterface::class);
    $messageRepository->shouldReceive('findById')->once()->with('missing-message')->andReturn(null);

    $job = new BroadcastMessageSentJob('missing-message');
    $job->handle($messageRepository);

    Event::assertNotDispatched(MessageSentBroadcast::class);
});

it('broadcasts conversation read updates', function () {
    Event::fake();

    $job = new BroadcastConversationReadJob('conversation-1', 'user-1', ['message-1', 'message-2']);
    $job->handle();

    Event::assertDispatched(ConversationReadBroadcast::class, function (ConversationReadBroadcast $event): bool {
        return $event->conversationId === 'conversation-1'
            && $event->readerId === 'user-1'
            && $event->messageIds === ['message-1', 'message-2'];
    });
});

it('broadcasts typing indicator updates', function () {
    Event::fake();

    $job = new BroadcastTypingIndicatorJob('conversation-1', 'user-1', true, '2026-02-28T10:00:00+00:00');
    $job->handle();

    Event::assertDispatched(TypingIndicatorBroadcast::class, function (TypingIndicatorBroadcast $event): bool {
        return $event->conversationId === 'conversation-1'
            && $event->userId === 'user-1'
            && $event->isTyping === true
            && $event->occurredAt === '2026-02-28T10:00:00+00:00';
    });
});
