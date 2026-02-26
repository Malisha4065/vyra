<?php

namespace App\Domains\Content\Exceptions;

use Exception;

class EmptyCommentException extends Exception
{
    public function __construct()
    {
        parent::__construct('A comment cannot be empty.');
    }
}
