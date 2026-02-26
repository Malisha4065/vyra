<?php

namespace App\Domains\Notification\Repositories;

use App\Domains\Notification\Models\UserNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserNotificationRepositoryInterface
{
    public function create(array $attributes): UserNotification;

    public function findById(string $id): ?UserNotification;

    public function markAsRead(UserNotification $notification): UserNotification;

    public function markAllAsRead(string $userId): int;

    public function paginateForUser(string $userId, int $perPage = 20, bool $unreadOnly = false): LengthAwarePaginator;
}
