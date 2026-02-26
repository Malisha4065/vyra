<?php

namespace App\Domains\Communication\Actions;

use App\Domains\Communication\Data\ListConversationMessagesData;
use App\Domains\Communication\Repositories\MessageRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListConversationMessagesAction
{
    public function __construct(
        private readonly MessageRepositoryInterface $messageRepository,
    ) {}

    public function __invoke(ListConversationMessagesData $data): LengthAwarePaginator
    {
        return $this->messageRepository->paginateForConversation(
            conversationId: $data->conversation_id,
            perPage: $data->per_page,
        );
    }
}
