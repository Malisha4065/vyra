<?php

namespace App\Domains\Content\Data;

use Spatie\LaravelData\Data;

class PublishCommentData extends Data
{
    public function __construct(
        public readonly string $post_id,
        public readonly string $body,
        public readonly ?string $parent_comment_id = null,
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'post_id' => ['required', 'string', 'exists:posts,id'],
            'body' => ['required', 'string', 'max:1000'],
            'parent_comment_id' => ['nullable', 'string', 'exists:comments,id'],
        ];
    }

    public function normalizedBody(): string
    {
        return trim($this->body);
    }
}
