<?php
declare(strict_types=1);

namespace api\controllers\actions\currencies\params;

use common\controllers\params\Params;

class CurrencyListParams extends Params
{

    public string $from;

    public string $to;

    public function rules(): array
    {
        return [
            ['from', 'date', 'format' => 'php:Y-m-d'],
            ['to', 'date', 'format' => 'php:Y-m-d'],
            [['from', 'to'], 'required'],
            ['from','compare','compareAttribute'=>'to','operator'=>'<='],
        ];
    }
}
