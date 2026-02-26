<?php

namespace App\Domains\Communication\Exceptions;

use RuntimeException;

class DirectConversationBlockedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Cannot start a conversation because one of the users has blocked the other.');
    }
}
