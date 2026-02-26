<?php

namespace App\Domains\Identity\Exceptions;

use Exception;

class InvalidCredentialsException extends Exception
{
    public function __construct()
    {
        parent::__construct('The provided credentials are incorrect.');
    }
}
