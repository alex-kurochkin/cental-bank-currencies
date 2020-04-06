<?php

namespace common\components;

use yii\base\InvalidCallException;
use yii\base\UnknownPropertyException;

/**
 * Class ModelMapper
 */
abstract class ModelMapper
{

    /** @var array Model attributes */
    protected $_attributes = [];
    /** @var array Model relations */
    protected $_relations = [];
    /** @var bool Whether the model is mutable */
    protected $mutable;

    /**
     * ModelMapper constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes, bool $mutable = false)
    {
        $allowAttributes = array_flip(static::mapping());

        foreach ($attributes as $name => $value) {
            if (isset($allowAttributes[$name])) {
                $this->_attributes[$allowAttributes[$name]] = $value;
            }
        }
        $this->mutable = $mutable;
    }

    /**
     * Mapping class attributes to fields from a table
     *
     * @return array
     */
    abstract public static function mapping(): array;

    /**
     * This method should be overridden to declare related objects.
     *
     * There are four types of relations that may exist between two active record objects:
     * <ul>
     * <li>HAS_ONE: e.g. a member has at most one profile;</li>
     * <li>HAS_MANY: e.g. a team has many members;</li>
     * </ul>
     *
     * Each kind of related objects is defined in this method as an array with the following elements:
     * <pre>
     * 'varName' => ['relationType', 'classNameOfRepository', ['repositoryMethodName', $this->relationId]]
     * </pre>
     */
    public function relations(): array
    {
        return [];
    }

    /**
     * Get all attributes
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->_attributes;
    }

    /**
     * Extract raw objects's values to an array
     *
     * @return array
     */
    public function extract(): array
    {
        return array_intersect_key(array_flip(static::mapping()), $this->_attributes);
    }

    public function toEntity(): array
    {
        $attributes = $this->getAttributes();

        foreach (static::mapping() as $key => $value) {
            $result[$value] = $attributes[$key] ?? null;
        }

        return $result;
    }

    /**
     * @param string $name
     *
     * @return mixed
     * @throws UnknownPropertyException
     */
    public function __get(string $name)
    {
        if (!isset(static::mapping()[$name])) {
            throw new UnknownPropertyException('Getting unknown property: ' . static::class . '::' . $name);
        }

        return $this->_attributes[$name] ?? null;
    }

    /**
     * @param string $name
     * @param mixed $value
     *
     * @throws UnknownPropertyException
     */
    public function __set(string $name, $value)
    {
        if (!$this->mutable) {
            throw new InvalidCallException('Setting read-only property: ' . static::class . '::' . $name);
        }
        if (!isset(static::mapping()[$name])) {
            throw new UnknownPropertyException('Setting unknown property: ' . static::class . '::' . $name);
        }

        $this->_attributes[$name] = $value;
    }

    /**
     * @param string $relationName
     *
     * @return ModelMapper|ModelMapper[]|null
     */
    protected function lazyLoad(string $relationName)
    {
        if (array_key_exists($relationName, $this->_relations)) {
            return $this->_relations[$relationName];
        }
        if (array_key_exists($relationName, $this->_attributes)) {
            return $this->_attributes[$relationName];
        }

        if (!isset($this->relations()[$relationName])) {
            throw new InvalidCallException('Setting read-only property: ' . static::class . '::' . $relationName);
        }

        $this->_relations[$relationName] = call_user_func($this->relations()[$relationName]);

        return $this->_relations[$relationName];
    }
}