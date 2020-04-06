<?php

namespace common\domain\mappers\dto\converters;

use common\domain\mappers\ValueConverter;
use common\domain\utils\Strings;
use DateTime;
use DateTimeZone;

class StringToDateTimeConverter implements ValueConverter
{

    private const ISO8601 = 'Y-m-d\TH:i:sP';

    public function toExternal($string)
    {
        if ($string === null) {
            return null;
        }

        return DateTime::createFromFormat(self::ISO8601, $string, new DateTimeZone('UTC'));
    }

    public function toInternal($dateTime): ?string
    {
        /** @var DateTime $dateTime */
        if ($dateTime === null) {
            return null;
        }

        $value = $dateTime->format(self::ISO8601);

        // workaround for bug https://bugs.php.net/bug.php?id=60288
        if (Strings::startsWith($value, '-0001-11-30')) {
            return '0000-00-00T00:00:00+0000';
        }

        return $value;
    }
}
