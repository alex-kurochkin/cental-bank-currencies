<?php

namespace common\domain\persistence\converters;

use DateTime;
use DateTimeZone;

/**
 * Class DateTimeConverter
 * @package common\domain\persistence\converters
 * @deprecated
 */
class DateTimeConverter
{

    const DATETIME_FORMAT = 'Y-m-d H:i:s';

    public static function toArValue(?DateTime $date): ?string
    {
        if ($date == null) {
            return null;
        }

        $value = $date->format(self::DATETIME_FORMAT);

        // workaround for bug https://bugs.php.net/bug.php?id=60288
        if ($value == '-0001-11-30 00:00:00') {
            return '0000-00-00 00:00:00';
        }

        return $value;
    }

    public static function toModelValue(?string $value): ?DateTime
    {
        if ($value == null) {
            return null;
        }

        return DateTime::createFromFormat(self::DATETIME_FORMAT, $value, new DateTimeZone("UTC"));
    }
}
