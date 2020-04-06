<?php

namespace common\components\validators;

/** @inheritdoc */
class UrlValidator extends \yii\validators\UrlValidator
{

    /** @inheritdoc */
    public function validateAttribute($model, $attribute)
    {
        $oldValue = $model->$attribute;
        $value = ltrim($oldValue, '/');
        if ($value !== $oldValue) {
            $this->defaultScheme = 'https';
            $model->$attribute = $value;
        }

        parent::validateAttribute($model, $attribute);
    }
}
