<?php

namespace common\controllers\dtos;

class ListResponseDto extends ResponseDto
{

    /** @var array */
    public $data = [];

    /** @var int */
    public $total = 0;

    public function __construct(array $data, int $total = 0)
    {
        $this->data = $data;
        $this->total = $total;
    }
}
