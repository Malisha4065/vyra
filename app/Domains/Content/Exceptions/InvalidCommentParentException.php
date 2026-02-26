<?php

namespace App\Domains\Content\Exceptions;

use Exception;

class InvalidCommentParentException extends Exception
{
    public function __construct()
    {
        parent::__construct('Invalid parent comment for this post.');
    }
}
