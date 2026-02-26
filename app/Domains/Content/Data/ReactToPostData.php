<?php

namespace App\Domains\Content\Data;

use Spatie\LaravelData\Data;

class ReactToPostData extends Data
{
    public function __construct(
        public readonly string $post_id,
        public readonly string $type = 'like',
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'post_id' => ['required', 'string', 'exists:posts,id'],
            'type' => ['required', 'string', 'in:like,love,haha,wow,sad,angry'],
        ];
    }
}
