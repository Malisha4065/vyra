<?php

use App\Domains\Communication\Actions\SendMessageAction;
use App\Domains\Communication\Data\SendMessageData;
use App\Domains\Communication\Events\MessageSent;
use App\Domains\Communication\Exceptions\EmptyMessageException;
use App\Domains\Communication\Models\Conversation;
use App\Domains\Communication\Models\Message;
use App\Domains\Communication\Repositories\MessageRepositoryInterface;
use App\Domains\Identity\Models\User;
use Illuminate\Support\Facades\Event;

it('sends a message and dispatches message sent event', function () {
    Event::fake();

    $sender = new User();
    $sender->forceFill(['id' => 'user-1']);

    $conversation = new Conversation();
    $conversation->forceFill(['id' => 'conversation-1']);

    $message = new Message();
    $message->forceFill(['id' => 'message-1']);

    $messageRepository = mock(MessageRepositoryInterface::class);
    $messageRepository->shouldReceive('create')
        ->once()
        ->with('conversation-1', 'user-1', 'hello', ['client_id' => 'abc'])
        ->andReturn($message);

    $action = new SendMessageAction($messageRepository);

    $result = $action($sender, $conversation, SendMessageData::from([
        'conversation_id' => 'conversation-1',
        'body' => 'hello',
        'metadata' => ['client_id' => 'abc'],
    ]));

    expect($result->id)->toBe('message-1');

    Event::assertDispatched(MessageSent::class, function (MessageSent $event): bool {
        return $event->conversation->id === 'conversation-1'
            && $event->message->id === 'message-1';
    });
});

it('rejects empty message body', function () {
    $sender = new User();
    $sender->forceFill(['id' => 'user-1']);

    $conversation = new Conversation();
    $conversation->forceFill(['id' => 'conversation-1']);

    $messageRepository = mock(MessageRepositoryInterface::class);
    $messageRepository->shouldReceive('create')->never();

    $action = new SendMessageAction($messageRepository);

    $this->expectException(EmptyMessageException::class);

    $action($sender, $conversation, SendMessageData::from([
        'conversation_id' => 'conversation-1',
        'body' => '   ',
    ]));
});
