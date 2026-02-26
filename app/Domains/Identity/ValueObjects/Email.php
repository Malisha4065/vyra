<?php

namespace App\Domains\Identity\ValueObjects;

use InvalidArgumentException;
use Stringable;

final readonly class Email implements Stringable
{
    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));

        if (! filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email address: {$normalized}");
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

    /**
     * Extract the domain part of the email.
     */
    public function domain(): string
    {
        return substr($this->value, strpos($this->value, '@') + 1);
    }
}
