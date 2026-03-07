<?php

namespace App\Domains\Feed\Data;

use Spatie\LaravelData\Data;

class RebuildUserFeedCacheData extends Data
{
    public function __construct(
        public readonly string $user_id,
        public readonly int $limit = 500,
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'user_id' => ['required', 'string', 'exists:users,id'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:2000'],
        ];
    }
}
