<?php

namespace common\domain\mappers\dto\converters;

use common\domain\mappers\ValueConverter;
use common\domain\utils\Strings;
use DateTime;

class StringToDateConverter implements ValueConverter
{

    private const DATE_FORMAT = 'Y-m-d';

    public function toExternal($string)
    {
        if ($string === null) {
            return null;
        }

        $dateTime = DateTime::createFromFormat(self::DATE_FORMAT, $string);
        $dateTime->setTime(0, 0);
        return $dateTime;
    }

    public function toInternal($dateTime): ?string
    {
        if ($dateTime === null) {
            return null;
        }

        /** @var DateTime $dateTime */
        $value = $dateTime->format(self::DATE_FORMAT);

        // workaround for bug https://bugs.php.net/bug.php?id=60288
        if (Strings::startsWith($value, '-0001-11-30')) {
            return '0000-00-00T00:00:00+0000';
        }

        return $value;
    }
}
