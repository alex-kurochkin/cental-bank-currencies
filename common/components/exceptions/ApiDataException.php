<?php

namespace common\components\exceptions;

use yii\base\Model;
use yii\helpers\VarDumper;

/**
 * Class InvalidModelException
 * @package common\components\exceptions
 */
class ApiDataException extends Exception
{

    const REQUEST_DATA_VALIDATE = 51;

    const RESPONSE_DATA_VALIDATE = 31;
    const IN_TIME_REQUEST = 42;
    const UNAUTHORIZED = 41;
    const FORBIDDEN = 43;
    const UNKNOWN = 44;

    /**
     * @var Model
     */
    public $model;

    /**
     * Constructor.
     *
     * @param Model $model
     * @param string $message error message
     * @param integer $code error code
     * @param \Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct(Model $model = null, $code = 0, $message = null, \Exception $previous = null)
    {
        $this->model = $model;
        $message = $message ?: $this->getDefaultMessage();
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getDefaultMessage()
    {
        $errors = $this->model ? $this->model->getErrors() : [];
        return 'Data in model ' . get_class($this->model) . ' is invalid: ' . VarDumper::dumpAsString($errors);
    }
}
