<?php

namespace App\Domains\Identity\Exceptions;

use Exception;

class UsernameAlreadyTakenException extends Exception
{
    public function __construct(string $username)
    {
        parent::__construct("The username '{$username}' is already taken.");
    }
}
