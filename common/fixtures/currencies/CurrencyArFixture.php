<?php

namespace common\fixtures\currencies;

use api\models\currency\repositories\ars\CurrencyAr;
use yii\test\ActiveFixture;

class CurrencyArFixture extends ActiveFixture
{
    public $modelClass = CurrencyAr::class;
    public $dataFile = __DIR__ . '/data/CurrencyArs.php';
}
