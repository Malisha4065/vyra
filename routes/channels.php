<?php

use App\Domains\Communication\Repositories\ConversationRepositoryInterface;
use App\Domains\Identity\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{conversationId}', function (User $user, string $conversationId): bool {
    /** @var ConversationRepositoryInterface $conversationRepository */
    $conversationRepository = app(ConversationRepositoryInterface::class);

    return $conversationRepository->isParticipant($conversationId, $user->id);
});

Broadcast::channel('users.{userId}', function (User $user, string $userId): bool {
    return $user->id === $userId;
});
