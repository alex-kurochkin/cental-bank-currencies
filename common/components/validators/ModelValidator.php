<?php

namespace common\components\validators;

use common\components\Model;
use yii\helpers\ArrayHelper;

/**
 * Class ModelValidator validates parameters that contain a structure that can be described with a model.
 * You must set model, also you can set scenario for validation.
 * If your attribute must contain an array of structures which can be described by a model, set `isArray` parameter to
 * true Example:
 *
 * ```
 * [
 *        'geo_square' ,
 *        'model',
 *        'modelClass' => GeoSquareForm::class,
 *        'scenario' => 'default',
 *        'isArray' => true
 * ]
 *```
 *
 * @see \tests\unit\components\validators\ModelValidatorTest
 */
class ModelValidator extends Validator
{

    /** @var string A class of the model which will be used to validate attribute */
    public $modelClass;

    /** @var string Validation model scenario */
    public $scenario = Model::SCENARIO_DEFAULT;

    /** @var bool Validate like model if false or validate like array of models is true */
    public $isArray;

    /** @var array */
    public $childAttributes = [];

    /**
     * @var array|Model This variable keeps the forms, created when validating.
     * After validation is done, they are assigned to the model's attribute
     */
    protected $result = [];

    /** @var array */
    protected $errors = [];

    /** @var array */
    protected $_parentForm = [];

    /**
     * Validates the value with the [[$model]]
     *
     * @param $value
     *
     * @return array|bool|null
     */
    protected function validateValue($value)
    {
        if (!is_array($value)) {
            $message = 'This value must be an object!';
            $this->errors[] = $message;

            return [$message, []];
        }
        $this->errors = [];
        $this->result = [];
        if (!$this->isArray) {
            $value = [$value];
        }
        /** @var Model[] $value */
        foreach ($value as $subValue) {
            if (!is_array($subValue)) {
                if (!is_object($subValue)) {
                    $this->errors[] = 'This value must be an array!';
                    continue;
                } else {
                    $subValue = $subValue->toArray([], [], false);
                }
            }

            $subValue['parentForm'] = $this->_parentForm;
            $subForm = $this->createForm(array_merge($subValue, $this->childAttributes));
            if (!$subForm->validate()) {
                $this->errors[] = $subForm->errors;
            } else {
                $this->result[] = $subForm;
            }
        }
        if (empty($this->errors)) {
            if (!$this->isArray) {
                $this->result = $this->result[0];
            }
        } else {
            $message = 'Failed to validate the supplied data!';
            $this->errors[] = $message;

            return [$message, []];
        }
    }

    /** @inheritdoc */
    public function validate($value, &$error = null)
    {
        if (is_object($value)) {
            $value = ArrayHelper::toArray($value);
        }

        return parent::validate($value, $error);
    }

    /** @inheritdoc */
    public function validateAttribute($model, $attribute)
    {
        $this->_parentForm = [
            'model' => $model,
            'attribute' => $attribute,
        ];

        $value = $model->$attribute;
        if (is_object($value)) {
            $value = ArrayHelper::toArray($value);
        }

        $this->validateValue($value);
        if (!empty($this->errors)) {
            foreach ($this->errors as $error) {
                $model->addError($attribute, $error);
            }
        } else {
            $model->$attribute = $this->result;
        }
    }

    /**
     * Load model and set scenario
     *
     * @param array $data Data of attributes
     *
     * @return Model
     */
    private function createForm($data)
    {
        /** @var $model Model */
        $model = new $this->modelClass;
        $model->setScenario($this->scenario);
        $model->setAttributes($data);

        return $model;
    }
}
