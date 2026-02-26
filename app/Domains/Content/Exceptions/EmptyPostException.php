<?php

namespace App\Domains\Content\Exceptions;

use Exception;

class EmptyPostException extends Exception
{
    public function __construct()
    {
        parent::__construct('A post must include text or at least one media attachment.');
    }
}
