<?php

namespace common\domain\persistence\converters;

use DateTime;
use DateTimeZone;

/**
 * Class DateConverter
 * @package common\domain\persistence\converters
 * @deprecated
 */
class DateConverter
{

    const DATE_FORMAT = 'Y-m-d';

    public static function toArValue(?DateTime $date): ?string
    {
        if ($date == null) {
            return null;
        }

        return $date->format(self::DATE_FORMAT);
    }

    public static function toModelValue(?string $value): ?DateTime
    {
        if ($value == null) {
            return null;
        }

        return DateTime::createFromFormat(self::DATE_FORMAT, $value, new DateTimeZone("UTC"));
    }
}
