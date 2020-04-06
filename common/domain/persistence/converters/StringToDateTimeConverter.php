<?php

namespace common\domain\persistence\converters;

use common\domain\mappers\ValueConverter;
use \DateTime;
use \DateTimeZone;

class StringToDateTimeConverter implements ValueConverter
{

    const DATETIME_FORMAT = 'Y-m-d H:i:s';

    public function toExternal($string)
    {
        if ($string === null) {
            return null;
        }

        return DateTime::createFromFormat(self::DATETIME_FORMAT, $string, new DateTimeZone('UTC'));
    }

    public function toInternal($dateTime)
    {
        /** @var DateTime $dateTime */
        if ($dateTime === null) {
            return null;
        }

        $value = $dateTime->format(self::DATETIME_FORMAT);

        // workaround for bug https://bugs.php.net/bug.php?id=60288
        if ($value === '-0001-11-30 00:00:00') {
            return '0000-00-00 00:00:00';
        }

        return $value;
    }
}
