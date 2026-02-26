<?php

namespace App\Domains\Communication\Exceptions;

use RuntimeException;

class EmptyMessageException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Message body cannot be empty.');
    }
}
