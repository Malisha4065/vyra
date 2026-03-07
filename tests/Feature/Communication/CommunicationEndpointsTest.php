<?php

use App\Domains\Communication\Models\Conversation;
use App\Domains\Communication\Models\Message;
use App\Domains\Communication\Models\MessageReadReceipt;
use App\Domains\Communication\Actions\ListUserConversationsAction;
use App\Domains\Communication\Repositories\ConversationRepositoryInterface;
use App\Domains\Communication\Repositories\MessageReadReceiptRepositoryInterface;
use App\Domains\Communication\Repositories\MessageRepositoryInterface;
use App\Domains\Identity\Models\User;
use App\Domains\Identity\Repositories\UserRepositoryInterface;
use App\Domains\SocialGraph\Repositories\BlockRepositoryInterface;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Event;

it('starts a direct conversation via endpoint', function () {
    Event::fake();

    $authUser = new User();
    $authUser->forceFill([
        'id' => 'user-1',
        'username' => 'alice',
        'email' => 'alice@example.com',
        'password' => 'secret',
    ]);

    $targetUser = new User();
    $targetUser->forceFill([
        'id' => 'user-2',
        'username' => 'bob',
        'email' => 'bob@example.com',
        'password' => 'secret',
    ]);

    $conversation = new Conversation();
    $conversation->forceFill(['id' => 'conversation-1']);
    $participant = new \App\Domains\Communication\Models\ConversationParticipant();
    $participant->forceFill(['id' => 'participant-1']);

    $userRepository = mock(UserRepositoryInterface::class);
    $userRepository->shouldReceive('findById')->once()->with('user-2')->andReturn($targetUser);

    $conversationRepository = mock(ConversationRepositoryInterface::class);
    $conversationRepository->shouldReceive('findDirectByKey')->once()->with('user-1:user-2')->andReturn(null);
    $conversationRepository->shouldReceive('createDirect')->once()->with('user-1', 'user-1:user-2')->andReturn($conversation);
    $conversationRepository->shouldReceive('addParticipant')->once()->with('conversation-1', 'user-1')->andReturn($participant);
    $conversationRepository->shouldReceive('addParticipant')->once()->with('conversation-1', 'user-2')->andReturn($participant);

    $message = new Message();
    $message->forceFill(['id' => 'message-1']);

    $messageRepository = mock(MessageRepositoryInterface::class);
    $messageRepository->shouldReceive('create')->once()->with('conversation-1', 'user-1', 'hello')->andReturn($message);

    $blockRepository = mock(BlockRepositoryInterface::class);
    $blockRepository->shouldReceive('eitherBlocked')->twice()->with('user-1', 'user-2')->andReturn(false);

    $this->app->instance(UserRepositoryInterface::class, $userRepository);
    $this->app->instance(ConversationRepositoryInterface::class, $conversationRepository);
    $this->app->instance(MessageRepositoryInterface::class, $messageRepository);
    $this->app->instance(BlockRepositoryInterface::class, $blockRepository);

    $response = $this->actingAs($authUser)
        ->post(route('messages.conversations.direct.start'), [
            'target_user_id' => 'user-2',
            'initial_message' => 'hello',
        ]);

    $response->assertCreated();
    $response->assertJsonPath('data.conversation_id', 'conversation-1');
});

it('lists conversations with participant read state via endpoint', function () {
    $authUser = new User();
    $authUser->forceFill([
        'id' => 'user-1',
        'username' => 'alice',
        'email' => 'alice@example.com',
        'password' => 'secret',
    ]);

    $otherUser = new User();
    $otherUser->forceFill([
        'id' => 'user-2',
        'username' => 'bob',
        'email' => 'bob@example.com',
        'password' => 'secret',
    ]);

    $conversation = new Conversation();
    $conversation->forceFill([
        'id' => 'conversation-1',
        'type' => 'direct',
        'updated_at' => now(),
    ]);

    $authUser->setRelation('pivot', Pivot::fromAttributes($conversation, [
        'last_read_message_id' => 'message-1',
        'last_read_at' => now()->subMinute()->toDateTimeString(),
    ], 'conversation_participants'));
    $otherUser->setRelation('pivot', Pivot::fromAttributes($conversation, [
        'last_read_message_id' => null,
        'last_read_at' => null,
    ], 'conversation_participants'));

    $latestMessage = new Message();
    $latestMessage->forceFill([
        'id' => 'message-2',
        'conversation_id' => 'conversation-1',
        'sender_id' => 'user-2',
        'body' => 'hello',
        'created_at' => now(),
    ]);
    $latestMessage->setRelation('sender', $otherUser);

    $conversation->setRelation('participants', collect([$authUser, $otherUser]));
    $conversation->setRelation('latestMessage', $latestMessage);

    $action = mock(ListUserConversationsAction::class);
    $action->shouldReceive('__invoke')
        ->once()
        ->andReturn(new LengthAwarePaginator(
            items: [$conversation],
            total: 1,
            perPage: 20,
            currentPage: 1,
        ));

    $this->app->instance(ListUserConversationsAction::class, $action);

    $response = $this->actingAs($authUser)
        ->get(route('messages.conversations.index'));

    $response->assertOk();
    $response->assertJsonPath('data.0.participants.0.read_state.last_read_message_id', 'message-1');
    $response->assertJsonPath('data.0.latest_message.id', 'message-2');
});

it('lists conversation messages for participant via endpoint', function () {
    $authUser = new User();
    $authUser->forceFill([
        'id' => 'user-1',
        'username' => 'alice',
        'email' => 'alice@example.com',
        'password' => 'secret',
    ]);

    $conversation = new Conversation();
    $conversation->forceFill(['id' => 'conversation-1']);

    $conversationRepository = mock(ConversationRepositoryInterface::class);
    $conversationRepository->shouldReceive('findById')->once()->with('conversation-1')->andReturn($conversation);
    $conversationRepository->shouldReceive('isParticipant')->once()->with('conversation-1', 'user-1')->andReturn(true);

    $messageRepository = mock(MessageRepositoryInterface::class);
    $messageRepository->shouldReceive('paginateForConversation')
        ->once()
        ->with('conversation-1', 5)
        ->andReturn(new LengthAwarePaginator(
            items: [['id' => 'message-1', 'body' => 'hello']],
            total: 1,
            perPage: 5,
            currentPage: 1,
        ));

    $this->app->instance(ConversationRepositoryInterface::class, $conversationRepository);
    $this->app->instance(MessageRepositoryInterface::class, $messageRepository);

    $response = $this->actingAs($authUser)
        ->get(route('messages.conversations.messages.index', ['conversation' => 'conversation-1', 'per_page' => 5]));

    $response->assertOk();
    $response->assertJsonPath('meta.total', 1);
    $response->assertJsonPath('data.0.id', 'message-1');
});

it('serializes read receipts when listing conversation messages', function () {
    $authUser = new User();
    $authUser->forceFill([
        'id' => 'user-1',
        'username' => 'alice',
        'email' => 'alice@example.com',
        'password' => 'secret',
    ]);

    $otherUser = new User();
    $otherUser->forceFill([
        'id' => 'user-2',
        'username' => 'bob',
        'email' => 'bob@example.com',
        'password' => 'secret',
    ]);

    $conversation = new Conversation();
    $conversation->forceFill(['id' => 'conversation-1']);

    $conversationRepository = mock(ConversationRepositoryInterface::class);
    $conversationRepository->shouldReceive('findById')->once()->with('conversation-1')->andReturn($conversation);
    $conversationRepository->shouldReceive('isParticipant')->once()->with('conversation-1', 'user-1')->andReturn(true);

    $receipt = new MessageReadReceipt();
    $receipt->forceFill([
        'id' => 'receipt-1',
        'message_id' => 'message-1',
        'user_id' => 'user-2',
        'read_at' => now(),
    ]);

    $message = new Message();
    $message->forceFill([
        'id' => 'message-1',
        'conversation_id' => 'conversation-1',
        'sender_id' => 'user-1',
        'body' => 'hello',
        'created_at' => now(),
    ]);
    $message->setRelation('sender', $authUser);
    $message->setRelation('readReceipts', collect([$receipt]));

    $messageRepository = mock(MessageRepositoryInterface::class);
    $messageRepository->shouldReceive('paginateForConversation')
        ->once()
        ->with('conversation-1', 30)
        ->andReturn(new LengthAwarePaginator(
            items: [$message],
            total: 1,
            perPage: 30,
            currentPage: 1,
        ));

    $this->app->instance(ConversationRepositoryInterface::class, $conversationRepository);
    $this->app->instance(MessageRepositoryInterface::class, $messageRepository);

    $response = $this->actingAs($authUser)
        ->get(route('messages.conversations.messages.index', ['conversation' => 'conversation-1']));

    $response->assertOk();
    $response->assertJsonPath('data.0.read_receipts.0.user_id', 'user-2');
});

it('marks conversation messages as read via endpoint', function () {
    $authUser = new User();
    $authUser->forceFill([
        'id' => 'user-1',
        'username' => 'alice',
        'email' => 'alice@example.com',
        'password' => 'secret',
    ]);

    $conversation = new Conversation();
    $conversation->forceFill(['id' => 'conversation-1']);

    $conversationRepository = mock(ConversationRepositoryInterface::class);
    $conversationRepository->shouldReceive('findById')->once()->with('conversation-1')->andReturn($conversation);
    $conversationRepository->shouldReceive('isParticipant')->once()->with('conversation-1', 'user-1')->andReturn(true);
    $conversationRepository->shouldReceive('touchParticipantReadState')->once()->with('conversation-1', 'user-1', 'message-2');

    $messageRepository = mock(MessageRepositoryInterface::class);
    $messageRepository->shouldReceive('getUnreadMessageIdsForConversation')
        ->once()
        ->with('conversation-1', 'user-1')
        ->andReturn(['message-1', 'message-2']);

    $readReceiptRepository = mock(MessageReadReceiptRepositoryInterface::class);
    $readReceiptRepository->shouldReceive('markMessagesAsRead')->once()->andReturn(2);

    $this->app->instance(ConversationRepositoryInterface::class, $conversationRepository);
    $this->app->instance(MessageRepositoryInterface::class, $messageRepository);
    $this->app->instance(MessageReadReceiptRepositoryInterface::class, $readReceiptRepository);

    $response = $this->actingAs($authUser)
        ->put(route('messages.conversations.read', ['conversation' => 'conversation-1']));

    $response->assertOk();
    $response->assertJsonPath('data.marked_count', 2);
});

it('emits typing indicator via endpoint', function () {
    $authUser = new User();
    $authUser->forceFill([
        'id' => 'user-1',
        'username' => 'alice',
        'email' => 'alice@example.com',
        'password' => 'secret',
    ]);

    $conversation = new Conversation();
    $conversation->forceFill(['id' => 'conversation-1']);

    $conversationRepository = mock(ConversationRepositoryInterface::class);
    $conversationRepository->shouldReceive('findById')->once()->with('conversation-1')->andReturn($conversation);
    $conversationRepository->shouldReceive('isParticipant')->once()->with('conversation-1', 'user-1')->andReturn(true);
    $conversationRepository->shouldReceive('getParticipantIds')->once()->with('conversation-1')->andReturn(['user-1', 'user-2']);

    $blockRepository = mock(BlockRepositoryInterface::class);
    $blockRepository->shouldReceive('eitherBlocked')->once()->with('user-1', 'user-2')->andReturn(false);

    $this->app->instance(ConversationRepositoryInterface::class, $conversationRepository);
    $this->app->instance(BlockRepositoryInterface::class, $blockRepository);

    $response = $this->actingAs($authUser)
        ->post(route('messages.conversations.typing', ['conversation' => 'conversation-1']), [
            'is_typing' => true,
        ]);

    $response->assertStatus(202);
    $response->assertJsonPath('data.is_typing', true);
});
