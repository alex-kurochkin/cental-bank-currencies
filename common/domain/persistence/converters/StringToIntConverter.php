<?php

namespace common\domain\persistence\converters;

use common\domain\mappers\ValueConverter;

class StringToIntConverter implements ValueConverter
{

    public function toExternal($string)
    {
        if ($string == null) {
            return null;
        }

        return (int)$string;
    }

    public function toInternal($int)
    {
        if ($int == null) {
            return null;
        }

        return (string)$int;
    }
}
