<?php

namespace common\components;

use yii\log\Dispatcher;

class Logger extends Dispatcher
{

    protected $logger;

    public function __construct(array $config = [])
    {
        $this->logger = $this->getLogger();
        parent::__construct($config);
    }

    /**
     * Logs a debug message.
     * Trace messages are logged mainly for development purpose to see
     * the execution work flow of some code. This method will only log
     * a message when the application is in debug mode.
     * @param string|array $message the message to be logged. This can be a simple string or a more
     * complex data structure, such as array.
     * @param string $category the category of the message.
     * @since 2.0.14
     */
    public function debug($message, $category = 'application')
    {
        if (YII_DEBUG) {
            $this->logger->log($message, \yii\log\Logger::LEVEL_TRACE);
        }
    }

    /**
     * Logs an error message.
     * An error message is typically logged when an unrecoverable error occurs
     * during the execution of an application.
     * @param string|array $message the message to be logged. This can be a simple string or a more
     * complex data structure, such as array.
     * @param string $category the category of the message.
     */
    public function error($message, $category = 'application')
    {
        $this->logger->log($message, \yii\log\Logger::LEVEL_ERROR);
    }

    /**
     * Logs a warning message.
     * A warning message is typically logged when an error occurs while the execution
     * can still continue.
     * @param string|array $message the message to be logged. This can be a simple string or a more
     * complex data structure, such as array.
     * @param string $category the category of the message.
     */
    public function warning($message, $category = 'application')
    {
        $this->logger->log($message, \yii\log\Logger::LEVEL_WARNING);
    }

    /**
     * Logs an informative message.
     * An informative message is typically logged by an application to keep record of
     * something important (e.g. an administrator logs in).
     * @param string|array $message the message to be logged. This can be a simple string or a more
     * complex data structure, such as array.
     * @param string $category the category of the message.
     */
    public function info($message, $category = 'application')
    {
        $this->logger->log($message, \yii\log\Logger::LEVEL_INFO);
    }
}
