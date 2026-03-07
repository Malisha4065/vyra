<?php

namespace App\Domains\Notification\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Notification\Data\UpdateUserNotificationPreferencesData;
use App\Domains\Notification\Models\UserNotificationPreference;
use App\Domains\Notification\Repositories\NotificationPreferenceRepositoryInterface;

class UpdateUserNotificationPreferencesAction
{
    public function __construct(
        private readonly NotificationPreferenceRepositoryInterface $preferenceRepository,
    ) {}

    public function __invoke(User $user, UpdateUserNotificationPreferencesData $data): UserNotificationPreference
    {
        return $this->preferenceRepository->updateByUserId($user->id, [
            'social_enabled' => $data->social_enabled,
            'content_enabled' => $data->content_enabled,
            'communication_enabled' => $data->communication_enabled,
            'account_enabled' => $data->account_enabled,
        ]);
    }
}
