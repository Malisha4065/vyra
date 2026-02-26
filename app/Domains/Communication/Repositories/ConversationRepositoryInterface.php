<?php

namespace App\Domains\Communication\Repositories;

use App\Domains\Communication\Models\Conversation;
use App\Domains\Communication\Models\ConversationParticipant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ConversationRepositoryInterface
{
    public function findById(string $id): ?Conversation;

    public function findDirectByKey(string $directKey): ?Conversation;

    public function createDirect(string $creatorUserId, string $directKey): Conversation;

    public function addParticipant(string $conversationId, string $userId): ConversationParticipant;

    public function isParticipant(string $conversationId, string $userId): bool;

    /**
     * @return array<int, string>
     */
    public function getParticipantIds(string $conversationId): array;

    public function paginateForUser(string $userId, int $perPage = 20): LengthAwarePaginator;

    public function touchParticipantReadState(string $conversationId, string $userId, ?string $lastReadMessageId): void;
}
