<?php

namespace App\Domains\SocialGraph\Exceptions;

use Exception;

class AlreadyFollowingException extends Exception
{
    public function __construct()
    {
        parent::__construct('You are already following this user.');
    }
}
