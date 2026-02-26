<?php

namespace App\Domains\Communication\Repositories;

use App\Domains\Communication\Models\Conversation;
use App\Domains\Communication\Models\ConversationParticipant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentConversationRepository implements ConversationRepositoryInterface
{
    public function __construct(
        private readonly Conversation $conversation,
        private readonly ConversationParticipant $participant,
    ) {}

    public function findById(string $id): ?Conversation
    {
        return $this->conversation
            ->with(['participants.profile', 'latestMessage.sender.profile'])
            ->find($id);
    }

    public function findDirectByKey(string $directKey): ?Conversation
    {
        return $this->conversation
            ->where('type', 'direct')
            ->where('direct_key', $directKey)
            ->first();
    }

    public function createDirect(string $creatorUserId, string $directKey): Conversation
    {
        return $this->conversation->firstOrCreate(
            [
                'type' => 'direct',
                'direct_key' => $directKey,
            ],
            [
                'created_by' => $creatorUserId,
            ],
        );
    }

    public function addParticipant(string $conversationId, string $userId): ConversationParticipant
    {
        return $this->participant->firstOrCreate(
            [
                'conversation_id' => $conversationId,
                'user_id' => $userId,
            ],
            [
                'joined_at' => now(),
            ],
        );
    }

    public function isParticipant(string $conversationId, string $userId): bool
    {
        return $this->participant
            ->where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->exists();
    }

    public function getParticipantIds(string $conversationId): array
    {
        return $this->participant
            ->where('conversation_id', $conversationId)
            ->pluck('user_id')
            ->all();
    }

    public function paginateForUser(string $userId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->conversation
            ->whereHas('participantLinks', fn ($query) => $query->where('user_id', $userId))
            ->with(['participants.profile', 'latestMessage.sender.profile'])
            ->orderByDesc('updated_at')
            ->paginate($perPage);
    }

    public function touchParticipantReadState(string $conversationId, string $userId, ?string $lastReadMessageId): void
    {
        $this->participant
            ->where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->update([
                'last_read_message_id' => $lastReadMessageId,
                'last_read_at' => now(),
            ]);
    }
}
