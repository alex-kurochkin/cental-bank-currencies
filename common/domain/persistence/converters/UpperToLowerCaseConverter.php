<?php

namespace common\domain\persistence\converters;

use common\domain\mappers\ValueConverter;
use common\domain\utils\Strings;

class UpperToLowerCaseConverter implements ValueConverter
{

    public function toExternal($upperCase)
    {
        if ($upperCase == null) {
            return null;
        }

        return Strings::toLowerCase($upperCase);
    }

    public function toInternal($lowerCase)
    {
        if ($lowerCase == null) {
            return null;
        }

        return Strings::toUpperCase($lowerCase);
    }
}
