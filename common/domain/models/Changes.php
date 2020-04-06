<?php

namespace common\domain\models;

class Changes
{

    /**
     * @param Change[] $changes
     *
     * @return array with new and old value maps
     */
    public static function toArray(array $changes)
    {
        return \common\domain\utils\Changes::toArray($changes);
    }
}
