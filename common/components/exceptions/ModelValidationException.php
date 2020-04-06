<?php

namespace common\components\exceptions;

use yii\base\Model;

/**
 * There were errors while validating a model.
 */
class ModelValidationException extends Exception
{

    /** @var Model Model in which validation error occurred */
    protected $_model;

    /**
     * @param Model $model
     */
    function __construct(Model $model)
    {
        $this->_model = $model;
        $this->message = "Validation failed! Errors:\n" . var_export($model->getErrors(), true);
        parent::__construct();
    }

    /**
     * Model, in which validation error occurred
     * @return Model
     */
    public function getModel(): ?Model
    {
        return $this->_model;
    }
}
