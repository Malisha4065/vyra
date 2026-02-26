<?php

namespace App\Domains\Content\ValueObjects;

use InvalidArgumentException;
use Stringable;

final readonly class MediaUrl implements Stringable
{
    public string $value;

    public function __construct(string $value)
    {
        $normalized = trim($value);

        if (! filter_var($normalized, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid media URL.');
        }

        $this->value = $normalized;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
