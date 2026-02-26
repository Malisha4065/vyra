<?php

namespace App\Domains\Identity\Exceptions;

use Exception;

class EmailAlreadyTakenException extends Exception
{
    public function __construct(string $email)
    {
        parent::__construct("The email '{$email}' is already registered.");
    }
}
