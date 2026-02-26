<?php

namespace App\Domains\Notification\Data;

use Spatie\LaravelData\Data;

class MarkNotificationReadData extends Data
{
    public function __construct(
        public readonly string $notification_id,
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'notification_id' => ['required', 'string', 'exists:user_notifications,id'],
        ];
    }
}
