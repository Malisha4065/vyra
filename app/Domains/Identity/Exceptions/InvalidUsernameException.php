<?php

namespace App\Domains\Identity\Exceptions;

use Exception;

class InvalidUsernameException extends Exception
{
    public static function invalidLength(): self
    {
        return new self('Username must be between 3 and 30 characters.');
    }

    public static function invalidCharacters(): self
    {
        return new self('Username may only contain lowercase letters, numbers, and underscores.');
    }

    public static function consecutiveUnderscores(): self
    {
        return new self('Username may not contain consecutive underscores.');
    }

    public static function boundaryUnderscore(): self
    {
        return new self('Username may not start or end with an underscore.');
    }
}
