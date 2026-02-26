<?php

namespace App\Domains\SocialGraph\Exceptions;

use Exception;

class UserBlockedException extends Exception
{
    public function __construct()
    {
        parent::__construct('This action cannot be performed due to a block relationship.');
    }
}
