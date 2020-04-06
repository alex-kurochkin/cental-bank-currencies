<?php

namespace common\components\exceptions;

class RedisException extends Exception
{

    /** @inheritdoc */
    public function getName(): string
    {
        return 'Redis Exception';
    }
}
