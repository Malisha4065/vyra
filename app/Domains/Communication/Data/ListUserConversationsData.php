<?php

namespace App\Domains\Communication\Data;

use Spatie\LaravelData\Data;

class ListUserConversationsData extends Data
{
    public function __construct(
        public readonly int $per_page = 20,
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
