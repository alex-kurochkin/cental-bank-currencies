<?php

namespace common\domain\persistence\exceptions;

use common\domain\exceptions\AppException;
use common\domain\utils\Strings;

class EntityNotFoundException extends AppException
{

    /** @var string */
    public $type;

    /** @var mixed|null */
    public $args;

    public function __construct(string $type, ...$args)
    {
        parent::__construct('Not found.');

        $this->type = $type;
        $this->args = $args;
    }

    public function __toString()
    {
        return Strings::format('Entity {} not found: {}', $this->type, join(', ', $this->args));
    }
}
