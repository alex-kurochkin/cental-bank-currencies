<?php

namespace common\domain\mappers;

use common\domain\exceptions\AppException;

abstract class ObjectMapper
{
    /** @var string */
    public $internalType;

    /** @var string */
    public $externalType;

    /** @var string[] */
    public $mapping;

    /** @var string[] */
    private $converters = [];

    /** @var PropertyMapping[] */
    private $propertyMappingCache = [];

    /**
     * ObjectMapper constructor.
     * @param string   $internalType
     * @param string[] $mapping
     * @param string   $externalType
     * @param string[] $converters
     */
    protected function __construct(string $internalType, array $mapping, string $externalType, array $converters)
    {
        $this->internalType = $internalType;
        $this->mapping = $mapping;
        $this->externalType = $externalType;
        $this->converters = $this->prepareConverters($converters);
    }

    protected function toManyExternals(array $internals)
    {
        $externals = [];
        foreach ($internals as $internal) {
            $externals[] = $this->toOneExternal($internal, new $this->externalType());
        }

        return $externals;
    }

    protected function toManyInternals(array $externals)
    {
        $internals = [];
        foreach ($externals as $external) {
            $internals[] = $this->toOneInternal($external, new $this->internalType());
        }

        return $internals;
    }

    /**
     * @param mixed|null $internal
     * @param mixed      $external
     * @return mixed|null
     */
    protected function toOneExternal($internal, $external = null)
    {
        if ($internal == null) {
            return null;
        }

        if ($external == null) {
            $external = new $this->externalType();
        }

        foreach ($this->mapping as $internalName => $externalEntry) {
            $propertyMapping = $this->getPropertyMapping($internalName, $externalEntry);
            if (!$propertyMapping) {
                continue;
            }

            $external->{$propertyMapping->externalName} = $propertyMapping->externalConverter
                ? $propertyMapping->externalConverter->toExternal($internal->{$propertyMapping->internalName})
                : $internal->{$propertyMapping->internalName};
        }

        return $external;
    }

    /**
     * @param mixed|null $external
     * @param mixed      $internal
     * @return mixed|null
     */
    protected function toOneInternal($external, $internal = null)
    {
        if ($external == null) {
            return null;
        }

        if ($internal == null) {
            $internal = new $this->internalType();
        }

        foreach ($this->mapping as $internalName => $externalEntry) {
            $propertyMapping = $this->getPropertyMapping($internalName, $externalEntry);
            if (!$propertyMapping || $propertyMapping->externalReadOnly) {
                continue;
            }

            $internal->{$propertyMapping->internalName} = $propertyMapping->externalConverter
                ? $propertyMapping->externalConverter->toInternal($external->{$propertyMapping->externalName})
                : $external->{$propertyMapping->externalName};
        }

        return $internal;
    }

    protected function getPropertyMapping($internalName, $externalEntry): ?PropertyMapping
    {
        if (isset($this->propertyMappingCache[$internalName])) {
            return $this->propertyMappingCache[$internalName];
        }

        if (is_string($internalName)) {
            $externalName = $this->getExternalName($externalEntry);
            $externalConverter = $this->getExternalConverter($externalEntry);
            $externalReadOnly = $this->isExternalReadOnly($externalEntry);
            if (!$externalName) {
                $this->propertyMappingCache[$internalName] = null;
                return null;
            }
        } else {
            // numeric index means both names are equal
            $internalName = $externalName = $externalEntry;
            $externalConverter = null;
            $externalReadOnly = false;
        }

        $propertyMapping = new PropertyMapping($internalName, $externalName, $externalReadOnly, $externalConverter);
        $this->propertyMappingCache[$internalName] = $propertyMapping;
        return $propertyMapping;
    }

    private function getExternalName($entry)
    {
        if (empty($entry)) {
            return null;
        }

        if (is_string($entry)) {
            return $entry;
        }

        if (is_array($entry)) {
            return $entry[0];
        }

        return null;
    }

    private function getExternalConverter($entry): ?ValueConverter
    {
        if (!is_array($entry)) {
            return null;
        }

        if (empty($entry[1])) {
            return null;
        }

        $alias = $entry[1];
        if (empty($this->converters[$alias])) {
            throw new AppException("Unknown converter type: $alias");
        }

        $valueConverter = $this->converters[$alias];
        return is_object($valueConverter) ? $valueConverter : new $valueConverter();
    }

    private function isExternalReadOnly($entry)
    {
        if (!is_array($entry)) {
            return false;
        }

        if (empty($entry[2])) {
            return false;
        }

        // ignore if false
        return !$entry[2];
    }

    private function prepareConverters(array $converters):array
    {
        $result = [];
        foreach ($converters as $alias => $converter) {
            // string alias
            if (is_string($alias)) {
                $result[$alias] = $converter;
                continue;
            }

            // no alias here - we should create one
            // converter instance
            if (is_object($converter)) {
                $alias = get_class($converter);
                $result[$alias] = $converter;
                continue;
            }

            // class
            $result[$converter] = $converter;
        }

        return $result;
    }
}
