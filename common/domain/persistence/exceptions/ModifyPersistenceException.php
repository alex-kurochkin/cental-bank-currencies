<?php

namespace common\domain\persistence\exceptions;

use Throwable;

class ModifyPersistenceException extends PersistenceException
{

    public $entity;

    public function __construct(string $message, $entity, Throwable $case = null)
    {
        parent::__construct($message, $case);
        $this->entity = $entity;
    }
}
