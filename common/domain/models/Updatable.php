<?php

namespace common\domain\models;

use common\domain\utils\Objects;

abstract class Updatable implements Preparable
{

    private $watchFieldChanges = [];
    private $alwaysIncludeFields = [];
    private $oldValues = [];

    public function __construct(array $watchFieldChanges, array $alwaysIncludeFields = [])
    {
        $this->watchFieldChanges = $watchFieldChanges;
        $this->alwaysIncludeFields = $alwaysIncludeFields;
    }

    public function isChanged($fieldName)
    {
        if (!in_array($fieldName, $this->watchFieldChanges)) {
            return false;
        }

        // no old values for this $fieldName
        if (!array_key_exists($fieldName, $this->oldValues)) {
            return true;
        }

        $newValue = $this->unwrapValue($this->getValue($fieldName));
        $oldValue = $this->unwrapValue($this->oldValues[$fieldName]);
        return !Objects::equals($oldValue, $newValue);
    }

    /**
     * @deprecated
     */
    public function prepareForUpdate()
    {
        $this->prepare();
    }

    public function prepare()
    {
        $this->oldValues = $this->createValueMap();
    }

    /**
     * @param bool $changedOnly
     *
     * @return Change[]
     */
    public function getChanges($changedOnly = true)
    {
        $changes = [];
        $extra = [];
        $oldValues = $this->oldValues;
        foreach ($this->watchFieldChanges as $fieldName) {
            $newValue = $this->unwrapValue($this->getValue($fieldName));
            $oldValue = array_key_exists($fieldName, $oldValues)
                ? $this->unwrapValue($oldValues[$fieldName])
                : $this->unwrapValue($this->getValue($fieldName));

            $equals = Objects::equals($oldValue, $newValue);
            $include = in_array($fieldName, $this->alwaysIncludeFields);

            if (!$changedOnly || !$equals) {
                $changes[] = new Change($fieldName, $oldValue, $newValue);
            } else if ($include) {
                $extra[] = new Change($fieldName, $oldValue, $newValue);
            }
        }

        return !empty($changes) ? array_merge($changes, $extra) : [];
    }

    private function createValueMap()
    {
        $valueMap = [];
        foreach ($this->watchFieldChanges as $fieldName) {
            $valueMap[$fieldName] = $this->getValue($fieldName);
        }
        return $valueMap;
    }

    protected function getValue($fieldName)
    {
        return $this->$fieldName;
    }

    private function unwrapValue($value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = $this->unwrapValue($val);
            }
            return $value;
        }

        if (is_object($value)) {
            return $this->unwrapValue((array)$value);
        }

        return $value;
    }
}
