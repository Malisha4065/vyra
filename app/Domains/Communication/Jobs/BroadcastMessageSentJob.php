<?php

namespace App\Domains\Communication\Jobs;

use App\Domains\Communication\Events\Broadcast\MessageSentBroadcast;
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

        event(new MessageSentBroadcast(
            conversationId: $message->conversation_id,
            message: [
                'id' => $message->id,
                'conversation_id' => $message->conversation_id,
                'sender_id' => $message->sender_id,
                'body' => $message->body,
                'metadata' => $message->metadata ?? [],
                'created_at' => $message->created_at?->toIso8601String(),
                'sender' => [
                    'id' => $message->sender?->id,
                    'username' => $message->sender?->username,
                ],
            ],
        ));
    }
}
