<?php

namespace App\Domains\Communication\Data;

use Spatie\LaravelData\Data;

class SendMessageData extends Data
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public readonly string $conversation_id,
        public readonly string $body,
        public readonly array $metadata = [],
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'conversation_id' => ['required', 'string', 'exists:conversations,id'],
            'body' => ['required', 'string', 'max:10000'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
