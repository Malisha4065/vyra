<?php

namespace App\Domains\Content\Data;

use Spatie\LaravelData\Data;

class ListCommentRepliesData extends Data
{
    public function __construct(
        public readonly string $comment_id,
        public readonly int $per_page = 20,
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'comment_id' => ['required', 'string', 'exists:comments,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
