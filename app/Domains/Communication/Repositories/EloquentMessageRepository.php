<?php

namespace App\Domains\Communication\Repositories;

use App\Domains\Communication\Models\Conversation;
use App\Domains\Communication\Models\Message;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentMessageRepository implements MessageRepositoryInterface
{
    public function __construct(
        private readonly Message $message,
        private readonly Conversation $conversation,
    ) {}

    public function create(string $conversationId, string $senderId, string $body, array $metadata = []): Message
    {
        $message = $this->message->create([
            'conversation_id' => $conversationId,
            'sender_id' => $senderId,
            'body' => $body,
            'metadata' => $metadata,
        ]);

        $this->conversation
            ->where('id', $conversationId)
            ->update(['updated_at' => now()]);

        return $message;
    }

    public function findById(string $id): ?Message
    {
        return $this->message
            ->with(['sender.profile', 'readReceipts'])
            ->find($id);
    }

    public function paginateForConversation(string $conversationId, int $perPage = 30): LengthAwarePaginator
    {
        return $this->message
            ->where('conversation_id', $conversationId)
            ->with(['sender.profile', 'readReceipts'])
            ->orderBy('created_at')
            ->paginate($perPage);
    }

    public function getUnreadMessageIdsForConversation(string $conversationId, string $userId, int $limit = 500): array
    {
        return $this->message
            ->where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $userId)
            ->whereDoesntHave('readReceipts', fn ($query) => $query->where('user_id', $userId))
            ->orderBy('created_at')
            ->limit($limit)
            ->pluck('id')
            ->all();
    }
}
