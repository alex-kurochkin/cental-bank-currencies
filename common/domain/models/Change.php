<?php

namespace common\domain\models;

class Change
{

    const PROPERTY_NAME = 'propertyName';
    const OLD_VALUE = 'oldValue';
    const NEW_VALUE = 'newValue';

    /** @var string */
    public $propertyName;

    /** @var mixed */
    public $oldValue;

    /** @var mixed */
    public $newValue;

    public function __construct($fieldName, $oldValue, $newValue)
    {
        $this->propertyName = $fieldName;
        $this->oldValue = $oldValue;
        $this->newValue = $newValue;
    }

    /**
     * @return array with new and old value maps
     */
    public function getValues()
    {
        $newValues = [$this->propertyName => $this->newValue];
        $oldValues = [$this->propertyName => $this->oldValue];
        return [$newValues, $oldValues];
    }
}
