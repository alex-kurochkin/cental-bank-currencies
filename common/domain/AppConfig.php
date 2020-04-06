<?php

namespace common\domain;

use \Yii;

class AppConfig
{

    public static function getCorsOrigin(): string
    {
        return Yii::$app->params['corsOrigin'];
    }
}
