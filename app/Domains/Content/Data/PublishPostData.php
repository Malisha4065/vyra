<?php

namespace App\Domains\Content\Data;

use Spatie\LaravelData\Data;

class PublishPostData extends Data
{
    public function __construct(
        public readonly ?string $body = null,
        /** @var array<int, array{url: string, mime_type?: string|null, size_bytes?: int|null, kind?: string|null}> */
        public readonly array $media = [],
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'body' => ['nullable', 'string', 'max:2000'],
            'media' => ['nullable', 'array', 'max:10'],
            'media.*.url' => ['required', 'string', 'url', 'max:2048'],
            'media.*.mime_type' => ['nullable', 'string', 'max:255'],
            'media.*.size_bytes' => ['nullable', 'integer', 'min:1'],
            'media.*.kind' => ['nullable', 'string', 'in:image,video'],
        ];
    }

    public function normalizedBody(): ?string
    {
        if ($this->body === null) {
            return null;
        }

        $normalized = trim($this->body);

        return $normalized === '' ? null : $normalized;
    }
}
