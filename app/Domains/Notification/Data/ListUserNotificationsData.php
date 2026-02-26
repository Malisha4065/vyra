<?php

namespace App\Domains\Notification\Data;

use Spatie\LaravelData\Data;

class ListUserNotificationsData extends Data
{
    public function __construct(
        public readonly int $per_page = 20,
        public readonly bool $unread_only = false,
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'unread_only' => ['nullable', 'boolean'],
        ];
    }
}
