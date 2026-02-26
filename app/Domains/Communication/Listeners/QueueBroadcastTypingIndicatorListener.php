<?php

namespace App\Domains\Communication\Listeners;

use App\Domains\Communication\Events\ConversationTypingUpdated;
use App\Domains\Communication\Jobs\BroadcastTypingIndicatorJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueueBroadcastTypingIndicatorListener implements ShouldQueue
{
    public string $queue = 'default';

    public function handle(ConversationTypingUpdated $event): void
    {
        BroadcastTypingIndicatorJob::dispatch(
            conversationId: $event->conversationId,
            userId: $event->userId,
            isTyping: $event->isTyping,
            occurredAt: $event->occurredAt,
        )->onQueue('communication');
    }
}
