<?php

namespace App\Domains\Communication\Actions;

use App\Domains\Communication\Data\SendMessageData;
use App\Domains\Communication\Events\MessageSent;
use App\Domains\Communication\Exceptions\EmptyMessageException;
use App\Domains\Communication\Models\Conversation;
use App\Domains\Communication\Models\Message;
use App\Domains\Communication\Repositories\MessageRepositoryInterface;
use App\Domains\Identity\Models\User;

class SendMessageAction
{
    public function __construct(
        private readonly MessageRepositoryInterface $messageRepository,
    ) {}

    public function __invoke(User $sender, Conversation $conversation, SendMessageData $data): Message
    {
        $body = trim($data->body);

        if ($body === '') {
            throw new EmptyMessageException();
        }

        $message = $this->messageRepository->create(
            conversationId: $conversation->id,
            senderId: $sender->id,
            body: $body,
            metadata: $data->metadata,
        );

        event(new MessageSent($conversation, $message));

        return $message;
    }
}
