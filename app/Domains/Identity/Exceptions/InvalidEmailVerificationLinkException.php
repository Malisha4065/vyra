<?php

namespace App\Domains\Identity\Exceptions;

use RuntimeException;

class InvalidEmailVerificationLinkException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('This email verification link is invalid.');
    }
}
