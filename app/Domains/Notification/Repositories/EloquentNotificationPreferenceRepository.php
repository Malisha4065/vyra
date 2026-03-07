<?php

namespace App\Domains\Notification\Repositories;

use App\Domains\Notification\Models\UserNotificationPreference;

class EloquentNotificationPreferenceRepository implements NotificationPreferenceRepositoryInterface
{
    public function __construct(
        private readonly UserNotificationPreference $model,
    ) {}

    public function findByUserId(string $userId): ?UserNotificationPreference
    {
        return $this->model->where('user_id', $userId)->first();
    }

    public function firstOrCreateByUserId(string $userId): UserNotificationPreference
    {
        return $this->model->firstOrCreate(
            ['user_id' => $userId],
            $this->defaultAttributes(),
        );
    }

    public function updateByUserId(string $userId, array $attributes): UserNotificationPreference
    {
        $preference = $this->firstOrCreateByUserId($userId);

        $preference->update($attributes);

        return $preference->fresh();
    }

    public function allowsType(string $userId, string $type): bool
    {
        $preference = $this->firstOrCreateByUserId($userId);
        $column = $this->columnForType($type);

        return (bool) $preference->{$column};
    }

    /**
     * @return array<string, bool>
     */
    private function defaultAttributes(): array
    {
        return [
            'social_enabled' => true,
            'content_enabled' => true,
            'communication_enabled' => true,
            'account_enabled' => true,
        ];
    }

    private function columnForType(string $type): string
    {
        if (str_starts_with($type, 'social.')) {
            return 'social_enabled';
        }

        if (str_starts_with($type, 'content.')) {
            return 'content_enabled';
        }

        if (str_starts_with($type, 'communication.')) {
            return 'communication_enabled';
        }

        return 'account_enabled';
    }
}
