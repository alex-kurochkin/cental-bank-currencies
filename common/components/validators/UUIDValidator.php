<?php

namespace common\components\validators;

use yii\base\Model;
use yii\base\NotSupportedException;
use yii\validators\RegularExpressionValidator;

/**
 * Determines whether the attribute value is a valid universally unique identifier of version 4.
 */
class UUIDValidator extends RegularExpressionValidator
{
    /** @inheritdoc */
    public $pattern = '/^[\da-f]{8}([\da-f]{4}){3}[\da-f]{12}$/';

    /** @var string the regular expression to be matched with delimiter */
    // protected $patternWithDelimiter = '/^[\da-f]{8}(-[\da-f]{4}){3}-[\da-f]{12}$/';

    /** @inheritdoc */
    public $useDelimiter = false;

    /** @inheritdoc */
    public $message = '{attribute} is not a valid UUID v4';

    /** @var bool Validate like model if false or validate like array of models is true */
    public $isArray;

    /**
     * Validates a single attribute.
     *
     * Converts the string to lowercase
     *
     * @param Model $model the data model to be validated
     * @param string $attribute the name of the attribute to be validated.
     * @throws NotSupportedException
     */
    public function validateAttribute($model, $attribute)
    {
        if (!$this->isArray) {
            $model->$attribute = [$model->$attribute];
        }
        $arr = [];
        foreach ($model->$attribute as $value) {
            if (!is_string($value)) {
                $this->addError($model, $attribute, $this->message);

                return;
            }
            $value = strtolower($value);
            $result = $this->validateValue($value);
            if (!empty($result)) {
                $this->addError($model, $attribute, $result[0], $result[1]);
            } else {
                $arr[] = $value;
            }
        }
        $model->$attribute = $this->isArray ? $arr : reset($arr);
    }
}
