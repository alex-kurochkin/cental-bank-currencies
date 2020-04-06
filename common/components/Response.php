<?php

namespace common\components;

use InvalidArgumentException;
use stdClass;
use Yii;

/**
 * The Response class represents response to any action within api module.
 *
 * All Controllers descending from Controller and ApiController in the APIModule automatically use this response.
 *
 * For successful operations you should return from actions normally. Output data will be converted into json or
 * other format if requested by external client.
 *
 * In case of error the following construct should be used:
 *
 * ```php
 *      return $this->response->error(Response::ERROR_BAD_REQUEST,'Property ID is not a valid property ID');
 * ```
 *
 */
class Response extends \yii\web\Response
{

    /** Request was handled successfully */
    const SUCCESS = 200;
    /** There was a serious internal error */
    const ERROR_INTERNAL = 500;
    /** User must be authorized to access the resource*/
    const ERROR_UNAUTHORIZED = 401;
    /** Some of the data did not pass validation */
    const ERROR_VALIDATION = 422;
    /** A resource or entity was not found */
    const ERROR_NOT_FOUND = 404;
    /** User does not have the rights to access the resource */
    const ERROR_FORBIDDEN = 403;
    /** Input data was corrupted or request was not made in the correct fashion*/
    const ERROR_BAD_REQUEST = 400;
    /** This HTTP method is not supported for this action */
    const ERROR_BAD_METHOD = 406;
    /** Request content type is not supported */
    const ERROR_CONTENT_TYPE = 405;
    /** The requested resource was not modified since the last time it was accessed */
    const STATUS_NOT_MODIFIED = 304;
    /** The request has been accepted for processing, but the processing has not been completed */
    const ACCEPTED = 202;

    /** Will format the data according to the rest standard and other rules decided on (see guide)*/
    const MODE_REST = 0;
    /** Will return html */
    const MODE_WEB = 1;
    /** List of status codes which correspond to the result which may return data in response body */
    const STATUSES_WITH_DATA = [
        self::SUCCESS,
        self::STATUS_NOT_MODIFIED,
        self::ACCEPTED,
    ];
    /** @var bool Determines whether all REST requests will send back CORS header. For testing purposes only */
    public $corsFree = false;
    /** @var int Contains the code of an error if one occurred or zero if everything went well. See ERROR_* constants */
    protected $_errorCode = self::SUCCESS;
    /** @var array Contains additional information about an error */
    protected $_errorDetails;
    /** @var string A message that should be shown to the end-user */
    protected $_userMessage;
    /** @var string A link to the web-page providing additional information */
    protected $_moreInfo;
    /** @var integer Current mode */
    protected $_mode = self::MODE_WEB;
    /**
     * @var array Descriptions of the errors defined for the API
     */
    private $_statusDescriptions = [
        self::SUCCESS => 'Request was handled successfully',
        self::ERROR_INTERNAL => 'There was a serious internal error',
        self::ERROR_UNAUTHORIZED => 'User must be authorized to access the resource',
        self::ERROR_VALIDATION => 'Some of the data did not pass validation',
        self::ERROR_NOT_FOUND => 'A resource or entity was not found',
        self::ERROR_FORBIDDEN => 'User does not have the rights to access the resource',
        self::ERROR_BAD_REQUEST => 'Input data was corrupted or request was not made in the correct fashion',
        self::ERROR_BAD_METHOD => 'This HTTP method is not allowed for the requested resource',
        self::ERROR_CONTENT_TYPE => 'API can not serve the requested content type',
        self::STATUS_NOT_MODIFIED => 'The resource was not modified since the last time it was accessed',
        self::ACCEPTED => 'The request has been accepted for processing, but the processing has not been completed',
    ];

    /** @inheritdoc */
    public function init(): void
    {
        parent::init();
//		$this->setMode(strpos(Yii::$app->request->url, '/api/') !== false ? self::MODE_REST : self::MODE_WEB);
    }

    /**
     * Compiles all available data into an ordered structure and writes it into the data property.
     */
    public function beforeSendHandler(): void
    {
        $error = error_get_last();
        if ($error && is_array($error) && static::errorIsFatal($error['type'])) {
            $this->data = $this->error(self::ERROR_INTERNAL);
        }
        if ($this->data === null) {
            $this->data = $this->returnSuccess();
        }

        if (is_countable($this->data) && count($this->data) > 100) {
            Yii::debug('Result count over 100: ' . count($this->data));
            Yii::debug(array_slice($this->data, 0, 100));
        } else {
            Yii::debug($this->data);
        }

        if (is_array($this->data) || is_object($this->data)) {
            if (!isset($this->data['result'])) {
                $this->data = [
                    'result' => 'success',
                    'data' => $this->data,
                ];
            }
            $this->content = json_encode($this->data, JSON_UNESCAPED_UNICODE);
            $this->data = null;
        }

        if (YII_DEBUG && $this->corsFree && !$this->isSent && !$this->headers->has('Access-Control-Allow-Origin')) {
            $this->headers->add('Access-Control-Allow-Origin', Yii::$app->request->headers->get('origin'));
        }
    }

    /**
     * Determines whether the given error code is one of the errors that stop execution.
     *
     * @param int $code Error code
     *
     * @return boolean
     */
    public static function errorIsFatal(int $code): bool
    {
        $fatalErrors = E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_PARSE | E_USER_ERROR | E_RECOVERABLE_ERROR;

        return $code & $fatalErrors === $fatalErrors;
    }

    /**
     * Composes the error response
     *
     * @param integer $errorCode
     * @param string $errorDetails
     * @param string $userMessage
     * @param string $moreInfo
     *
     * @return array Detailed error data to be send to user. Needs to be returned by action
     */
    public function error(int $errorCode, $errorDetails = null, string $userMessage = null, string $moreInfo = null): array
    {
        $data['result'] = 'error';
        $data['error_code'] = $errorCode;
        $data['error_description'] = $this->getErrorDescription($errorCode);
        $this->_errorCode = $errorCode;
        $this->statusCode = $errorCode;

        if (!empty($errorDetails)) {
            $data['error_details'] = $errorDetails;
            $this->_errorDetails = $errorDetails;
            $data['message'] = $errorDetails;
        }
        if (!empty($userMessage)) {
            $data['user_message'] = $userMessage;
            $this->_userMessage = $userMessage;
        }
        if (!empty($moreInfo)) {
            $data['more_info'] = $moreInfo;
            $this->_moreInfo = $moreInfo;
        }

        return $data;
    }

    /**
     * Retrieves a description of an error with a given code
     *
     * @param int $errorCode Error code Response::ERROR_*
     *
     * @return string Human readable error description
     */
    private function getErrorDescription(int $errorCode): string
    {
        return $this->_statusDescriptions[$errorCode];
    }

    /**
     * Return empty successful answer
     *
     * @return stdClass
     */
    public function returnSuccess(): stdClass
    {
        return (object)['result' => 'success'];
    }

    /**
     * Compose a non-error response
     *
     * @param integer $statusCode One of [[self]] constants
     * @param string|array $data Optional response content
     *
     * @return array|null|string Composed response
     */
    public function compose(int $statusCode, $data = null)
    {
        $this->statusCode = $statusCode;
        if (is_null($data)) {
            return null;
        }

        if (!in_array($statusCode, self::STATUSES_WITH_DATA)) {
            throw new InvalidArgumentException('This status does not support returning data!');
        }

        return $data;
    }

    /**
     * Whether response is in the rest mode
     * @return bool
     */
    public function isRest(): bool
    {
        return $this->_mode === self::MODE_REST;
    }

    /**
     * Retrieves the current mode
     * @return int One of self::MODE_*
     */
    public function getMode(): int
    {
        return $this->_mode;
    }

    /**
     * Changes the response mode
     *
     * @param integer $mode One of MODE_* constants
     */
    public function setMode(int $mode): void
    {
        $this->off(Response::EVENT_BEFORE_SEND, [$this, 'beforeSendHandler']);
        if ($mode === self::MODE_REST) {
            $this->format = Response::FORMAT_JSON;
            $this->on(Response::EVENT_BEFORE_SEND, [$this, 'beforeSendHandler']);
            $this->_mode = self::MODE_REST;
        } else {
            $this->format = Response::FORMAT_HTML;
            $this->_mode = self::MODE_WEB;
        }
    }
}
