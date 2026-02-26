<?php

namespace App\Domains\Feed\Data;

use Spatie\LaravelData\Data;

class GetUserFeedData extends Data
{
    public function __construct(
        public readonly string $user_id,
        public readonly int $limit = 50,
        public readonly ?int $before_score = null,
        public readonly int $hybrid_per_author = 5,
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'user_id' => ['required', 'string', 'exists:users,id'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:200'],
            'before_score' => ['nullable', 'integer', 'min:1'],
            'hybrid_per_author' => ['nullable', 'integer', 'min:1', 'max:20'],
        ];
    }
}
