<?php

namespace common\controllers\dtos;

abstract class ResponseDto
{

    const ERROR = 'error';
    const SUCCESS = 'success';

    /** @var string */
    public $result = self::SUCCESS;

    /** @var string|null */
    public $message = null;
}
