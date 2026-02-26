<?php

namespace App\Domains\Communication\Actions;

use App\Domains\Communication\Data\EmitTypingIndicatorData;
use App\Domains\Communication\Events\ConversationTypingUpdated;
use App\Domains\Communication\Models\Conversation;
use App\Domains\Identity\Models\User;
use Carbon\CarbonImmutable;

class EmitTypingIndicatorAction
{
    public function __invoke(User $user, Conversation $conversation, EmitTypingIndicatorData $data): void
    {
        event(new ConversationTypingUpdated(
            conversationId: $conversation->id,
            userId: $user->id,
            isTyping: $data->is_typing,
            occurredAt: CarbonImmutable::now()->toIso8601String(),
        ));
    }
}
