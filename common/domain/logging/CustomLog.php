<?php

namespace common\domain\logging;

use common\domain\utils\Strings;

abstract class CustomLog
{
    const DIRECTORY = '/app/runtime/logs';

    /** @var bool */
    private static $debug = false;

    /**
     * @return bool
     */
    public static function isDebug(): bool
    {
        return self::$debug;
    }

    /**
     * @param bool $debug
     */
    public static function setDebug(bool $debug): void
    {
        self::$debug = $debug;
    }

    public static function error(string $message, ...$values)
    {
        self::log('ERROR', Strings::format($message, ...$values));
    }

    public static function warn(string $message, ...$values)
    {
        self::log('WARN', Strings::format($message, ...$values));
    }

    public static function info(string $message, ...$values)
    {
        self::log('INFO', Strings::format($message, ...$values));
    }

    public static function debug(string $message, ...$values)
    {
        if (!self::$debug && !YII_ENV_DEV) {
            return;
        }

        self::log('DEBUG', Strings::format($message, ...$values));
    }

    private static function log(string $level, string $message)
    {
        self::createDirectoryIfNeeded();

        $time = date('Y-m-d H:i:s');
        $userIp = (@$_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        error_log("$level: $time  $userIp  $message\n", 3, static::getPath());
    }

    private static function getPath()
    {
        $date = date('Y-m-d');
        return Strings::format('{}/{}_{}.log', self::DIRECTORY, static::getFilename(), $date);
    }

    private static function createDirectoryIfNeeded()
    {
        if (file_exists(self::DIRECTORY)) {
            return;
        }

        mkdir(self::DIRECTORY, 0777, true);
    }

    abstract protected static function getFilename(): string;
}
