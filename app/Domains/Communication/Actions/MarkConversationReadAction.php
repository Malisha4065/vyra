<?php

namespace App\Domains\Communication\Actions;

use App\Domains\Communication\Data\MarkConversationReadData;
use App\Domains\Communication\Events\ConversationMessagesRead;
use App\Domains\Communication\Models\Conversation;
use App\Domains\Communication\Repositories\ConversationRepositoryInterface;
use App\Domains\Communication\Repositories\MessageReadReceiptRepositoryInterface;
use App\Domains\Communication\Repositories\MessageRepositoryInterface;
use App\Domains\Identity\Models\User;
use Carbon\CarbonImmutable;

class MarkConversationReadAction
{
    public function __construct(
        private readonly MessageRepositoryInterface $messageRepository,
        private readonly MessageReadReceiptRepositoryInterface $readReceiptRepository,
        private readonly ConversationRepositoryInterface $conversationRepository,
    ) {}

    public function __invoke(User $reader, Conversation $conversation, MarkConversationReadData $data): int
    {
        $unreadMessageIds = $this->messageRepository->getUnreadMessageIdsForConversation(
            conversationId: $conversation->id,
            userId: $reader->id,
        );

        $readAt = CarbonImmutable::now();

        $inserted = $this->readReceiptRepository->markMessagesAsRead(
            messageIds: $unreadMessageIds,
            userId: $reader->id,
            readAt: $readAt,
        );

        if ($unreadMessageIds !== []) {
            $this->conversationRepository->touchParticipantReadState(
                conversationId: $conversation->id,
                userId: $reader->id,
                lastReadMessageId: end($unreadMessageIds),
            );

            event(new ConversationMessagesRead(
                conversationId: $conversation->id,
                readerId: $reader->id,
                messageIds: $unreadMessageIds,
            ));
        }

        return $inserted;
    }
}
