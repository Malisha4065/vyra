<?php

namespace App\Domains\Notification\Repositories;

use App\Domains\Notification\Models\UserNotificationPreference;

interface NotificationPreferenceRepositoryInterface
{
    public function findByUserId(string $userId): ?UserNotificationPreference;

    public function firstOrCreateByUserId(string $userId): UserNotificationPreference;

    public function updateByUserId(string $userId, array $attributes): UserNotificationPreference;

    public function allowsType(string $userId, string $type): bool;
}
