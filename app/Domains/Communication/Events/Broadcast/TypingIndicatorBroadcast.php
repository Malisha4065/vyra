<?php

namespace App\Domains\Communication\Events\Broadcast;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class TypingIndicatorBroadcast implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    public function __construct(
        public readonly string $conversationId,
        public readonly string $userId,
        public readonly bool $isTyping,
        public readonly string $occurredAt,
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
        return 'communication.typing.updated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversationId,
            'user_id' => $this->userId,
            'is_typing' => $this->isTyping,
            'occurred_at' => $this->occurredAt,
        ];
    }
}
