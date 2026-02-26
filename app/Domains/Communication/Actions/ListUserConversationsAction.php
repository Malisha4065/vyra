<?php

namespace App\Domains\Communication\Actions;

use App\Domains\Communication\Data\ListUserConversationsData;
use App\Domains\Communication\Repositories\ConversationRepositoryInterface;
use App\Domains\Identity\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListUserConversationsAction
{
    public function __construct(
        private readonly ConversationRepositoryInterface $conversationRepository,
    ) {}

    public function __invoke(User $user, ListUserConversationsData $data): LengthAwarePaginator
    {
        return $this->conversationRepository->paginateForUser(
            userId: $user->id,
            perPage: $data->per_page,
        );
    }
}
