<?php
declare(strict_types=1);

namespace api\controllers\actions\currencies\dtos;

use common\controllers\dtos\BaseDto;

class CurrencyListDto extends BaseDto
{

    public const MAPPING = [
        'id',
        'valuteId',
        'numCode',
        'charCode',
        'name',
        'nominal',
        'value',
        'date' => ['date', 'date'],
    ];

    public int $id;

    public string $valuteId;

    public int $numCode;

    public string $charCode;

    public string $name;

    public int $nominal;

    public float $value;

    public string $date;
}
