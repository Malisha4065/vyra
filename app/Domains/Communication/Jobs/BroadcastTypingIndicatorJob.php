<?php

namespace App\Domains\Communication\Jobs;

use App\Domains\Communication\Events\Broadcast\TypingIndicatorBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class BroadcastTypingIndicatorJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $conversationId,
        public readonly string $userId,
        public readonly bool $isTyping,
        public readonly string $occurredAt,
    ) {}

    public function handle(): void
    {
        event(new TypingIndicatorBroadcast(
            conversationId: $this->conversationId,
            userId: $this->userId,
            isTyping: $this->isTyping,
            occurredAt: $this->occurredAt,
        ));
    }
}
