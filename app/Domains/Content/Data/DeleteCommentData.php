<?php

namespace App\Domains\Content\Data;

use Spatie\LaravelData\Data;

class DeleteCommentData extends Data
{
    public function __construct(
        public readonly string $comment_id,
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'comment_id' => ['required', 'string', 'exists:comments,id'],
        ];
    }
}
