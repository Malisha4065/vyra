<?php

namespace App\Domains\Communication\Listeners;

use App\Domains\Communication\Events\ConversationMessagesRead;
use App\Domains\Communication\Jobs\BroadcastConversationReadJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueueBroadcastConversationReadListener implements ShouldQueue
{
    public string $queue = 'default';

    public function handle(ConversationMessagesRead $event): void
    {
        BroadcastConversationReadJob::dispatch(
            conversationId: $event->conversationId,
            readerId: $event->readerId,
            messageIds: $event->messageIds,
        )->onQueue('communication');
    }
}
