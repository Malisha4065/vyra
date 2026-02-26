<?php

namespace App\Domains\Communication\Data;

use Spatie\LaravelData\Data;

class StartDirectConversationData extends Data
{
    public function __construct(
        public readonly string $target_user_id,
        public readonly ?string $initial_message = null,
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'target_user_id' => ['required', 'string'],
            'initial_message' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
