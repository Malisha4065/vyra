<?php

namespace App\Domains\Communication\Repositories;

use Carbon\CarbonImmutable;

interface MessageReadReceiptRepositoryInterface
{
    /**
     * @param array<int, string> $messageIds
     */
    public function markMessagesAsRead(array $messageIds, string $userId, CarbonImmutable $readAt): int;
}
