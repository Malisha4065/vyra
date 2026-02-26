<?php

use App\Domains\Communication\Actions\StartDirectConversationAction;
use App\Domains\Communication\Data\StartDirectConversationData;
use App\Domains\Communication\Events\MessageSent;
use App\Domains\Communication\Exceptions\CannotMessageSelfException;
use App\Domains\Communication\Exceptions\DirectConversationBlockedException;
use App\Domains\Communication\Models\Conversation;
use App\Domains\Communication\Models\ConversationParticipant;
use App\Domains\Communication\Models\Message;
use App\Domains\Communication\Repositories\ConversationRepositoryInterface;
use App\Domains\Communication\Repositories\MessageRepositoryInterface;
use App\Domains\Identity\Models\User;
use App\Domains\SocialGraph\Repositories\BlockRepositoryInterface;
use Illuminate\Support\Facades\Event;

it('creates a direct conversation and dispatches message sent for initial message', function () {
    Event::fake();

    $authUser = new User();
    $authUser->forceFill(['id' => 'user-1']);

    $targetUser = new User();
    $targetUser->forceFill(['id' => 'user-2']);

    $conversation = new Conversation();
    $conversation->forceFill(['id' => 'conversation-1']);

    $message = new Message();
    $message->forceFill(['id' => 'message-1']);

    $participant = new ConversationParticipant();
    $participant->forceFill(['id' => 'participant-1']);

    $blockRepository = mock(BlockRepositoryInterface::class);
    $blockRepository->shouldReceive('eitherBlocked')
        ->once()
        ->with('user-1', 'user-2')
        ->andReturn(false);

    $conversationRepository = mock(ConversationRepositoryInterface::class);
    $conversationRepository->shouldReceive('findDirectByKey')
        ->once()
        ->with('user-1:user-2')
        ->andReturn(null);
    $conversationRepository->shouldReceive('createDirect')
        ->once()
        ->with('user-1', 'user-1:user-2')
        ->andReturn($conversation);
    $conversationRepository->shouldReceive('addParticipant')
        ->once()
        ->with('conversation-1', 'user-1')
        ->andReturn($participant);
    $conversationRepository->shouldReceive('addParticipant')
        ->once()
        ->with('conversation-1', 'user-2')
        ->andReturn($participant);

    $messageRepository = mock(MessageRepositoryInterface::class);
    $messageRepository->shouldReceive('create')
        ->once()
        ->with('conversation-1', 'user-1', 'hello there')
        ->andReturn($message);

    $action = new StartDirectConversationAction(
        $conversationRepository,
        $messageRepository,
        $blockRepository,
    );

    $result = $action($authUser, $targetUser, StartDirectConversationData::from([
        'target_user_id' => 'user-2',
        'initial_message' => 'hello there',
    ]));

    expect($result->id)->toBe('conversation-1');

    Event::assertDispatched(MessageSent::class, function (MessageSent $event): bool {
        return $event->conversation->id === 'conversation-1'
            && $event->message->id === 'message-1';
    });
});

it('rejects direct conversation when users are blocked', function () {
    $authUser = new User();
    $authUser->forceFill(['id' => 'user-1']);

    $targetUser = new User();
    $targetUser->forceFill(['id' => 'user-2']);

    $blockRepository = mock(BlockRepositoryInterface::class);
    $blockRepository->shouldReceive('eitherBlocked')
        ->once()
        ->with('user-1', 'user-2')
        ->andReturn(true);

    $conversationRepository = mock(ConversationRepositoryInterface::class);
    $conversationRepository->shouldReceive('findDirectByKey')->never();

    $messageRepository = mock(MessageRepositoryInterface::class);
    $messageRepository->shouldReceive('create')->never();

    $action = new StartDirectConversationAction(
        $conversationRepository,
        $messageRepository,
        $blockRepository,
    );

    $this->expectException(DirectConversationBlockedException::class);

    $action($authUser, $targetUser, StartDirectConversationData::from([
        'target_user_id' => 'user-2',
    ]));
});

it('rejects direct conversation with self', function () {
    $authUser = new User();
    $authUser->forceFill(['id' => 'user-1']);

    $blockRepository = mock(BlockRepositoryInterface::class);
    $blockRepository->shouldReceive('eitherBlocked')->never();

    $conversationRepository = mock(ConversationRepositoryInterface::class);
    $messageRepository = mock(MessageRepositoryInterface::class);

    $action = new StartDirectConversationAction(
        $conversationRepository,
        $messageRepository,
        $blockRepository,
    );

    $this->expectException(CannotMessageSelfException::class);

    $action($authUser, $authUser, StartDirectConversationData::from([
        'target_user_id' => 'user-1',
    ]));
});
