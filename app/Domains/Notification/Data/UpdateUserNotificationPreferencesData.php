<?php

namespace App\Domains\Notification\Data;

use Spatie\LaravelData\Data;

class UpdateUserNotificationPreferencesData extends Data
{
    public function __construct(
        public readonly bool $social_enabled,
        public readonly bool $content_enabled,
        public readonly bool $communication_enabled,
        public readonly bool $account_enabled,
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'social_enabled' => ['required', 'boolean'],
            'content_enabled' => ['required', 'boolean'],
            'communication_enabled' => ['required', 'boolean'],
            'account_enabled' => ['required', 'boolean'],
        ];
    }
}
