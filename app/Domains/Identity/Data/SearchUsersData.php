<?php

namespace App\Domains\Identity\Data;

use Spatie\LaravelData\Data;

class SearchUsersData extends Data
{
    public function __construct(
        public readonly string $viewer_id,
        public readonly string $query,
        public readonly int $limit = 10,
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'viewer_id' => ['required', 'string', 'exists:users,id'],
            'query' => ['required', 'string', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:25'],
        ];
    }
}
