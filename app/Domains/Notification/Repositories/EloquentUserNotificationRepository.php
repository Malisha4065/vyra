<?php

namespace App\Domains\Notification\Repositories;

use App\Domains\Notification\Models\UserNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentUserNotificationRepository implements UserNotificationRepositoryInterface
{
    public function __construct(
        private readonly UserNotification $model,
    ) {}

    public function create(array $attributes): UserNotification
    {
        return $this->model->create($attributes);
    }

    public function findById(string $id): ?UserNotification
    {
        return $this->model->find($id);
    }

    public function unreadCount(string $userId): int
    {
        return $this->model
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    public function markAsRead(UserNotification $notification): UserNotification
    {
        if ($notification->read_at !== null) {
            return $notification;
        }

        $notification->update([
            'read_at' => now(),
        ]);

        return $notification->fresh();
    }

    public function markAllAsRead(string $userId): int
    {
        return $this->model
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function paginateForUser(string $userId, int $perPage = 20, bool $unreadOnly = false): LengthAwarePaginator
    {
        $query = $this->model
            ->where('user_id', $userId)
            ->latest();

        if ($unreadOnly) {
            $query->whereNull('read_at');
        }

        return $query->paginate($perPage);
    }
}
