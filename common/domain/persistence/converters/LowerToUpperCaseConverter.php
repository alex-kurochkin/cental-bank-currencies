<?php

namespace common\domain\persistence\converters;

use common\domain\mappers\ValueConverter;
use common\domain\utils\Strings;

class LowerToUpperCaseConverter implements ValueConverter
{

    public function toExternal($lowerCase)
    {
        if ($lowerCase == null) {
            return null;
        }

        return Strings::toUpperCase($lowerCase);
    }

    public function toInternal($upperCase)
    {
        if ($upperCase == null) {
            return null;
        }

        return Strings::toLowerCase($upperCase);
    }
}
