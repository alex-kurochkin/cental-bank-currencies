<?php

namespace common\domain\logging;

use common\domain\utils\Strings;
use Yii;

class Log
{
    public static function error(string $message, ...$values)
    {
        Yii::error(Strings::format($message, ...$values), static::getComponent());
    }

    public static function warn(string $message, ...$values)
    {
        Yii::warning(Strings::format($message, ...$values), static::getComponent());
    }

    public static function info(string $message, ...$values)
    {
        Yii::info(Strings::format($message, ...$values), static::getComponent());
    }

    public static function debug(string $message, ...$values)
    {
        Yii::debug(Strings::format($message, ...$values), static::getComponent());
    }

    protected static function getComponent(): string
    {
        return 'application';
    }
}
