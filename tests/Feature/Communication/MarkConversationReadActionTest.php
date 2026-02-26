<?php

use App\Domains\Communication\Actions\MarkConversationReadAction;
use App\Domains\Communication\Data\MarkConversationReadData;
use App\Domains\Communication\Events\ConversationMessagesRead;
use App\Domains\Communication\Models\Conversation;
use App\Domains\Communication\Repositories\ConversationRepositoryInterface;
use App\Domains\Communication\Repositories\MessageReadReceiptRepositoryInterface;
use App\Domains\Communication\Repositories\MessageRepositoryInterface;
use App\Domains\Identity\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Event;

it('marks unread messages as read and dispatches read event', function () {
    Event::fake();

    CarbonImmutable::setTestNow('2026-02-26T00:00:00Z');

    $reader = new User();
    $reader->forceFill(['id' => 'user-1']);

    $conversation = new Conversation();
    $conversation->forceFill(['id' => 'conversation-1']);

    $messageRepository = mock(MessageRepositoryInterface::class);
    $messageRepository->shouldReceive('getUnreadMessageIdsForConversation')
        ->once()
        ->with('conversation-1', 'user-1')
        ->andReturn(['message-1', 'message-2']);

    $readReceiptRepository = mock(MessageReadReceiptRepositoryInterface::class);
    $readReceiptRepository->shouldReceive('markMessagesAsRead')
        ->once()
        ->with(['message-1', 'message-2'], 'user-1', \Mockery::type(CarbonImmutable::class))
        ->andReturn(2);

    $conversationRepository = mock(ConversationRepositoryInterface::class);
    $conversationRepository->shouldReceive('touchParticipantReadState')
        ->once()
        ->with('conversation-1', 'user-1', 'message-2');

    $action = new MarkConversationReadAction(
        $messageRepository,
        $readReceiptRepository,
        $conversationRepository,
    );

    $result = $action($reader, $conversation, MarkConversationReadData::from([
        'conversation_id' => 'conversation-1',
    ]));

    expect($result)->toBe(2);

    Event::assertDispatched(ConversationMessagesRead::class, function (ConversationMessagesRead $event): bool {
        return $event->conversationId === 'conversation-1'
            && $event->readerId === 'user-1'
            && $event->messageIds === ['message-1', 'message-2'];
    });

    CarbonImmutable::setTestNow();
});
