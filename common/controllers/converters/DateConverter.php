<?php

namespace common\controllers\converters;

use DateTime;

class DateConverter
{
    const DATE_FORMAT = 'm-d-Y';

    public static function toDtoValue(?DateTime $date): ?string
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

        return DateTime::createFromFormat(self::DATE_FORMAT, $value);
    }
}
