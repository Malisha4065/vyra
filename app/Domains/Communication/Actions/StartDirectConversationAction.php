<?php

namespace App\Domains\Communication\Actions;

use App\Domains\Communication\Data\StartDirectConversationData;
use App\Domains\Communication\Events\MessageSent;
use App\Domains\Communication\Exceptions\CannotMessageSelfException;
use App\Domains\Communication\Exceptions\DirectConversationBlockedException;
use App\Domains\Communication\Exceptions\EmptyMessageException;
use App\Domains\Communication\Models\Conversation;
use App\Domains\Communication\Repositories\ConversationRepositoryInterface;
use App\Domains\Communication\Repositories\MessageRepositoryInterface;
use App\Domains\Communication\ValueObjects\DirectConversationKey;
use App\Domains\Identity\Models\User;
use App\Domains\SocialGraph\Repositories\BlockRepositoryInterface;

class StartDirectConversationAction
{
    public function __construct(
        private readonly ConversationRepositoryInterface $conversationRepository,
        private readonly MessageRepositoryInterface $messageRepository,
        private readonly BlockRepositoryInterface $blockRepository,
    ) {}

    public function __invoke(User $authUser, User $targetUser, StartDirectConversationData $data): Conversation
    {
        if ($authUser->id === $targetUser->id) {
            throw new CannotMessageSelfException();
        }

        if ($this->blockRepository->eitherBlocked($authUser->id, $targetUser->id)) {
            throw new DirectConversationBlockedException();
        }

        $directKey = DirectConversationKey::fromUsers($authUser, $targetUser);

        $conversation = $this->conversationRepository->findDirectByKey($directKey->value);

        if ($conversation === null) {
            $conversation = $this->conversationRepository->createDirect($authUser->id, $directKey->value);
            $this->conversationRepository->addParticipant($conversation->id, $authUser->id);
            $this->conversationRepository->addParticipant($conversation->id, $targetUser->id);
        }

        if ($data->initial_message !== null) {
            $body = trim($data->initial_message);

            if ($body === '') {
                throw new EmptyMessageException();
            }

            $message = $this->messageRepository->create(
                conversationId: $conversation->id,
                senderId: $authUser->id,
                body: $body,
            );

            event(new MessageSent($conversation, $message));
        }

        return $conversation;
    }
}
