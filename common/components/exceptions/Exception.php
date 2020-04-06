<?php

namespace common\components\exceptions;

class Exception extends \yii\base\Exception
{

    /** @inheritdoc */
    public function getName(): string
    {
        return 'Exception';
    }
}
