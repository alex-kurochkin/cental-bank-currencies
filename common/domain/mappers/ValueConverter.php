<?php

namespace common\domain\mappers;

interface ValueConverter
{

    public function toExternal($value);

    public function toInternal($value);
}
