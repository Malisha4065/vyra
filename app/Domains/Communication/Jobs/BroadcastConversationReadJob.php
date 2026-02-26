<?php

namespace App\Domains\Communication\Jobs;

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
        // Reverb broadcast will be implemented with frontend subscription channels.
    }
}
