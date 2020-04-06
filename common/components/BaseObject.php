<?php

namespace common\components;

/** @inheritDoc */
class BaseObject extends \yii\base\BaseObject
{

    /** @inheritDoc */
    public function __construct($config = [])
    {
        if (!empty($config)) {
            foreach ($config as $name => $value) {
                $this->{$name} = $value;
            }
        }
        parent::__construct([]);
    }
}
