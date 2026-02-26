<?php

namespace App\Domains\Communication\Jobs;

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
        // Reverb broadcast will be implemented with frontend subscription channels.
    }
}
