<?php

namespace App\Domains\Identity\ValueObjects;

use App\Domains\Identity\Exceptions\InvalidUsernameException;
use Stringable;

final readonly class Username implements Stringable
{
    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));

        if (strlen($normalized) < 3 || strlen($normalized) > 30) {
            throw InvalidUsernameException::invalidLength();
        }

        if (! preg_match('/^[a-z0-9_]+$/', $normalized)) {
            throw InvalidUsernameException::invalidCharacters();
        }

        if (str_contains($normalized, '__')) {
            throw InvalidUsernameException::consecutiveUnderscores();
        }

        if (str_starts_with($normalized, '_') || str_ends_with($normalized, '_')) {
            throw InvalidUsernameException::boundaryUnderscore();
        }

        $this->value = $normalized;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
