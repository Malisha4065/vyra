<?php

namespace App\Domains\Communication\Jobs;

use App\Domains\Communication\Events\Broadcast\ConversationReadBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class BroadcastConversationReadJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param array<int, string> $messageIds
     */
    public function __construct(
        public readonly string $conversationId,
        public readonly string $readerId,
        public readonly array $messageIds,
    ) {}

    public function handle(): void
    {
        event(new ConversationReadBroadcast(
            conversationId: $this->conversationId,
            readerId: $this->readerId,
            messageIds: $this->messageIds,
            readAt: now()->toIso8601String(),
        ));
    }
}
