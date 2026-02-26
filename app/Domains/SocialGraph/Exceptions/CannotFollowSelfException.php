<?php

namespace App\Domains\SocialGraph\Exceptions;

use Exception;

class CannotFollowSelfException extends Exception
{
    public function __construct()
    {
        parent::__construct('You cannot follow yourself.');
    }
}
