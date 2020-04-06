<?php

namespace common\controllers\dtos;

class ErrorResponseDto extends ResponseDto
{

    public function __construct($message)
    {
        $this->result = self::ERROR;
        $this->message = $message;
    }
}
