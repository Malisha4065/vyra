<?php

namespace App\Domains\Communication\Jobs;

use App\Domains\Communication\Repositories\MessageRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class BroadcastMessageSentJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $messageId,
    ) {}

    public function handle(MessageRepositoryInterface $messageRepository): void
    {
        $message = $messageRepository->findById($this->messageId);

        if ($message === null) {
            return;
        }

        // Reverb broadcast will be implemented with frontend subscription channels.
    }
}
