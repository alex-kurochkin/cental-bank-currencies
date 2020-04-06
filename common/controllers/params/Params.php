<?php

namespace common\controllers\params;

use yii\base\Model;

abstract class Params
    extends Model
{
    public function formName()
    {
        return '';
    }
}
