<?php

namespace common\controllers\dtos;

class ObjectResponseDto extends ResponseDto
{

    public $data;

    public function __construct($data = null)
    {
        $this->data = $data;
    }
}
