<?php

namespace App\Domains\Communication\Data;

use Spatie\LaravelData\Data;

class EmitTypingIndicatorData extends Data
{
    public function __construct(
        public readonly string $conversation_id,
        public readonly bool $is_typing = true,
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'conversation_id' => ['required', 'string', 'exists:conversations,id'],
            'is_typing' => ['nullable', 'boolean'],
        ];
    }
}
