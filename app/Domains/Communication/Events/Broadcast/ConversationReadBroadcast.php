<?php

namespace App\Domains\Communication\Events\Broadcast;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ConversationReadBroadcast implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    /**
     * @param array<int, string> $messageIds
     */
    public function __construct(
        public readonly string $conversationId,
        public readonly string $readerId,
        public readonly array $messageIds,
        public readonly string $readAt,
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel("conversation.{$this->conversationId}")];
    }

    public function broadcastAs(): string
    {
        return 'communication.read.updated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversationId,
            'reader_id' => $this->readerId,
            'message_ids' => $this->messageIds,
            'read_at' => $this->readAt,
        ];
    }
}
