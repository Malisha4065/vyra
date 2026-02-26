<?php

namespace App\Domains\Content\Data;

use Spatie\LaravelData\Data;

class GetPostReactionSummaryData extends Data
{
    public function __construct(
        public readonly string $post_id,
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'post_id' => ['required', 'string', 'exists:posts,id'],
        ];
    }
}
