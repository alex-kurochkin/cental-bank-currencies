<?php

namespace common\domain;

use common\domain\utils\Arrays;
use Sentry\State\Hub;
use Throwable;

class Sentry
{

    /** @var string[] */
    private static $ignored = [];

    public static function ignore(string...$exceptionClasses)
    {
        self::$ignored[] = Arrays::merge(self::$ignored, $exceptionClasses);
    }

    public static function isAllowed()
    {
        $testOrDev = in_array(SENTRY_ENV, ['test', 'dev']) || in_array(YII_ENV, ['test']);
        return !$testOrDev;
    }

    public static function captureException(Throwable $exception)
    {
        if (!self::isAllowed()) {
            return;
        }

        if (self::isIgnored($exception)) {
            return;
        }

        Hub::getCurrent()->captureException($exception);
    }

    private static function isIgnored(Throwable $exception): bool
    {
        $exceptionClass = get_class($exception);

        do {
            if (in_array($exceptionClass, self::$ignored)) {
                return true;
            }

            $exceptionClass = get_parent_class($exceptionClass);
        } while ($exceptionClass);

        return false;
    }
}
