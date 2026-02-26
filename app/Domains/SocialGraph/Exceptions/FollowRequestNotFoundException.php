<?php

namespace App\Domains\SocialGraph\Exceptions;

use Exception;

class FollowRequestNotFoundException extends Exception
{
    public function __construct()
    {
        parent::__construct('Follow request not found.');
    }
}
