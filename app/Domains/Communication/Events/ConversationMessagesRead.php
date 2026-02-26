<?php

namespace App\Domains\Communication\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationMessagesRead
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param array<int, string> $messageIds
     */
    public function __construct(
        public readonly string $conversationId,
        public readonly string $readerId,
        public readonly array $messageIds,
    ) {}
}
