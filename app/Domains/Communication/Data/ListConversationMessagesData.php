<?php

namespace App\Domains\Communication\Data;

use Spatie\LaravelData\Data;

class ListConversationMessagesData extends Data
{
    public function __construct(
        public readonly string $conversation_id,
        public readonly int $per_page = 30,
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'conversation_id' => ['required', 'string', 'exists:conversations,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
