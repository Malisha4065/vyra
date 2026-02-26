<?php

namespace App\Domains\Content\Exceptions;

use Exception;

class CommentNotFoundException extends Exception
{
    public function __construct()
    {
        parent::__construct('Comment not found.');
    }
}
