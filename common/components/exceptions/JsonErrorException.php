<?php

namespace common\components\exceptions;

use InvalidArgumentException;

/** Class JsonErrorException */
class JsonErrorException extends Exception
{

    /** @var array Error messages */
    public static $messages = [
        JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
        JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
        JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
        JSON_ERROR_SYNTAX => 'Syntax error. JSON is not valid',
        JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',
        JSON_ERROR_RECURSION => 'One or more recursive references in the value to be encoded',
        JSON_ERROR_INF_OR_NAN => 'One or more NAN or INF values in the value to be encoded',
        JSON_ERROR_UNSUPPORTED_TYPE => 'A value of a type that cannot be encoded was given',
    ];

    /** @inheritdoc */
    public function __construct($code, $previous = null)
    {
        if (isset(static::$messages[$code]) !== true) {
            throw new InvalidArgumentException(sprintf('%d is not a valid JSON error code.', $code));
        }
        parent::__construct(static::$messages[$code], $code, $previous);
    }
}
