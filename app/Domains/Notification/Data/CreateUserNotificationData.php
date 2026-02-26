<?php

namespace App\Domains\Notification\Data;

use Spatie\LaravelData\Data;

class CreateUserNotificationData extends Data
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        public readonly string $user_id,
        public readonly string $type,
        public readonly string $title,
        public readonly ?string $body = null,
        public readonly array $data = [],
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'user_id' => ['required', 'string', 'exists:users,id'],
            'type' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'data' => ['nullable', 'array'],
        ];
    }
}
