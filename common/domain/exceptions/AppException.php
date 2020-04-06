<?php

namespace common\domain\exceptions;

use RuntimeException;
use Throwable;

class AppException extends RuntimeException
{

    public function __construct($message = '', Throwable $case = null)
    {
        parent::__construct($message, 0, $case);
    }
}
