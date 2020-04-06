<?php

namespace common\domain\persistence\exceptions;

use common\domain\exceptions\AppException;
use Throwable;

class PersistenceException extends AppException
{

    public function __construct($message = '', Throwable $case = null)
    {
        parent::__construct($message, $case);
    }
}
