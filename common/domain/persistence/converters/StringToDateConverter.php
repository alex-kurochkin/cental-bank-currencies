<?php

namespace common\domain\persistence\converters;

use common\domain\mappers\ValueConverter;
use DateTime;

class StringToDateConverter implements ValueConverter
{

    const DATE_FORMAT = 'Y-m-d';

    public function toExternal($string)
    {
        if ($string == null) {
            return null;
        }

        $dateTime = DateTime::createFromFormat(self::DATE_FORMAT, $string);
        $dateTime->setTime(0, 0);
        return $dateTime;
    }

    public function toInternal($dateTime)
    {
        if ($dateTime == null) {
            return null;
        }

        /** @var DateTime $dateTime */
        return $dateTime->format(self::DATE_FORMAT);
    }
}
