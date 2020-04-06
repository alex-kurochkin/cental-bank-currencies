<?php

namespace common\domain\utils;

use common\domain\models\Change;

class Changes
{

    /**
     * @param Change[] $changes
     *
     * @return array with new and old value maps
     */
    public static function toArray(array $changes): array
    {
        $newValues = [];
        $oldValues = [];
        foreach ($changes as $change) {
            $newValues[$change->propertyName] = $change->newValue;
            $oldValues[$change->propertyName] = $change->oldValue;
        }
        return [$newValues, $oldValues];
    }

    /**
     * @param Change[] $changes
     * @param string   $propertyName
     * @return bool
     */
    public static function isPropertyChanged(array $changes, string $propertyName): bool
    {
        foreach ($changes as $change) {
            if ($change->propertyName == $propertyName) {
                return $change->oldValue != $change->newValue;
            }
        }

        return false;
    }
}
