<?php

namespace App\Domains\Communication\Repositories;

use App\Domains\Communication\Models\MessageReadReceipt;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

class EloquentMessageReadReceiptRepository implements MessageReadReceiptRepositoryInterface
{
    public function __construct(
        private readonly MessageReadReceipt $receipt,
    ) {}

    public function markMessagesAsRead(array $messageIds, string $userId, CarbonImmutable $readAt): int
    {
        if ($messageIds === []) {
            return 0;
        }

        $now = now();
        $rows = array_map(function (string $messageId) use ($userId, $readAt, $now): array {
            return [
                'id' => (string) Str::uuid(),
                'message_id' => $messageId,
                'user_id' => $userId,
                'read_at' => $readAt,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $messageIds);

        return $this->receipt->newQuery()->insertOrIgnore($rows);
    }
}
