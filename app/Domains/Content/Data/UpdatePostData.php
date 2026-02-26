<?php

namespace App\Domains\Content\Data;

use Spatie\LaravelData\Data;

class UpdatePostData extends Data
{
    public function __construct(
        public readonly string $post_id,
        public readonly string $body,
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'post_id' => ['required', 'string', 'exists:posts,id'],
            'body' => ['required', 'string', 'max:2000'],
        ];
    }

    public function normalizedBody(): string
    {
        return trim($this->body);
    }
}
