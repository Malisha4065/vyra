<?php

namespace App\Domains\Communication\Repositories;

use App\Domains\Communication\Models\Message;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface MessageRepositoryInterface
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function create(string $conversationId, string $senderId, string $body, array $metadata = []): Message;

    public function findById(string $id): ?Message;

    public function paginateForConversation(string $conversationId, int $perPage = 30): LengthAwarePaginator;

    /**
     * @return array<int, string>
     */
    public function getUnreadMessageIdsForConversation(string $conversationId, string $userId, int $limit = 500): array;
}
