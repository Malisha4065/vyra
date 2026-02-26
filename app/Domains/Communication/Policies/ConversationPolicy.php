<?php

namespace App\Domains\Communication\Policies;

use App\Domains\Communication\Models\Conversation;
use App\Domains\Communication\Repositories\ConversationRepositoryInterface;
use App\Domains\Identity\Models\User;
use App\Domains\SocialGraph\Repositories\BlockRepositoryInterface;

class ConversationPolicy
{
    public function __construct(
        private readonly ConversationRepositoryInterface $conversationRepository,
        private readonly BlockRepositoryInterface $blockRepository,
    ) {}

    public function createDirect(User $authUser, User $targetUser): bool
    {
        if ($authUser->id === $targetUser->id) {
            return false;
        }

        return ! $this->blockRepository->eitherBlocked($authUser->id, $targetUser->id);
    }

    public function view(User $authUser, Conversation $conversation): bool
    {
        return $this->conversationRepository->isParticipant($conversation->id, $authUser->id);
    }

    public function sendMessage(User $authUser, Conversation $conversation): bool
    {
        if (! $this->conversationRepository->isParticipant($conversation->id, $authUser->id)) {
            return false;
        }

        $participantIds = $this->conversationRepository->getParticipantIds($conversation->id);

        if (count($participantIds) === 2) {
            $otherUserId = $participantIds[0] === $authUser->id ? $participantIds[1] : $participantIds[0];

            return ! $this->blockRepository->eitherBlocked($authUser->id, $otherUserId);
        }

        return true;
    }

    public function markRead(User $authUser, Conversation $conversation): bool
    {
        return $this->conversationRepository->isParticipant($conversation->id, $authUser->id);
    }

    public function emitTyping(User $authUser, Conversation $conversation): bool
    {
        return $this->sendMessage($authUser, $conversation);
    }
}
