<?php

namespace App\Domains\Communication\Exceptions;

use RuntimeException;

class CannotMessageSelfException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('You cannot start a direct conversation with yourself.');
    }
}
