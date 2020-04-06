<?php

namespace common\domain\utils;

class Maps
{

    public static function fromKeysAndValue(array $keys, string $value): array
    {
        $map = [];
        foreach ($keys as $key) {
            $map[$key] = $value;
        }

        return $map;
    }
}
