<?php

namespace common\controllers\dtos;

use yii\base\Model;

abstract class BaseDto extends Model
{

    public function formName()
    {
        return '';
    }
}
