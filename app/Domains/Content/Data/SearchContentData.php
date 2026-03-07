<?php

namespace App\Domains\Content\Data;

use Spatie\LaravelData\Data;

class SearchContentData extends Data
{
    public function __construct(
        public readonly ?string $query = null,
        public readonly ?string $hashtag = null,
        public readonly int $limit = 20,
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'query' => ['nullable', 'string', 'max:100'],
            'hashtag' => ['nullable', 'string', 'max:60'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
