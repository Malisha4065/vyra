<?php

namespace App\Domains\Identity\Exceptions;

use RuntimeException;

class InvalidPasswordResetTokenException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('This password reset link is invalid or has expired.');
    }
}
