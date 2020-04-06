<?php

namespace common\domain\persistence\converters;

use common\domain\mappers\ValueConverter;

class JsonToValueConverter implements ValueConverter
{

    public function toExternal($string)
    {
        if ($string == null) {
            return null;
        }

        return json_decode($string);
    }

    public function toInternal($object)
    {
        if ($object == null) {
            return null;
        }

        return json_encode($object);
    }
}
