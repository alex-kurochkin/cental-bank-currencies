<?php

namespace common\domain\persistence\converters;

use common\domain\mappers\ValueConverter;

class IntToBooleanConverter implements ValueConverter
{

    public function toExternal($integer)
    {
        return (bool)$integer;
    }

    public function toInternal($boolean)
    {
        return (int)$boolean;
    }
}
