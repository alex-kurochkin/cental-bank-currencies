<?php

namespace common\components\traits;

use common\components\ActiveRecord;
use common\components\exceptions\Exception;
use common\components\exceptions\ModelValidationException;
use common\components\Model;
use common\components\validators\Validator;
use ArrayObject;
use yii\base\InvalidConfigException;

/**
 * Class ModelTrait
 * @package common\components
 * @property string $formName             Name of the form is set on a basis of class name of model and a current scenario
 * @property string $successMessage       Message to the client, in case of success of the model
 * @property array $attributesOnScenario Returns attribute values on the current scenario
 * @property array $usedAttributes       Returns a list of attributes that were actually assigned to the model
 */
trait ModelTrait
{

    /** @var boolean Determines whether to include null fields when exporting to array */
    protected $exportNullFields = false;
    /** @var int Error count for added common error message */
    protected $_errorCount = 0;
    /** @var string[] Required attribute names. Use the public method [[getRequiredRules]] for getting */
    protected $_required = [];
    /** @var string[] Array of all fields that were changed on mass-assignment, even if it is to have "null" value */
    protected $_usedAttributes = [];
    /** @var string[] Array of added magic fields (with *_id or *_uuid) */
    protected $_addedAttributes = [];

    /**
     * Returns an instance of the $class with loaded attributes and scenario.
     *
     * @param array $attributes Attributes to be loaded to the form.
     * @param string $scenario Scenario to be loaded to the form.
     * @param array $config Configuration data, which will be passed to the form constructor
     *
     * @return static
     */
    public static function make(array $attributes, string $scenario = null, array $config = []): self
    {
        $model = new static($config);
        $model->setScenario(!is_null($scenario) ? $scenario : static::SCENARIO_DEFAULT);
        $model->setAttributes($attributes);

        return $model;
    }

    /** @inheritdoc */
    public function setAttribute($name, $value)
    {
        parent::setAttribute($name, $value);
        $this->addToUsedAttributes($name);
    }

    /** @inheritdoc */
    public function setAttributes($values, $safeOnly = true)
    {
        if (empty($values)) {
            return;
        }

        parent::setAttributes($values, $safeOnly);

        $attributes = array_flip($this->activeAttributes());

        foreach ($values as $name => $value) {
            if (!isset($attributes[$name])) {
                continue;
            }
            $this->addToUsedAttributes($name);
        }
    }

    /**
     * Returns the attribute names that are safe to be massively assigned in the current scenario.
     *
     * @return string[] safe attribute names
     */
    public function safeAttributes(): array
    {
        /** @var Model|ActiveRecord $this */
        $attributes = parent::safeAttributes();

        return $this->uncheckRequiredAttributes($attributes);
    }

    /** @inheritdoc */
    public function onUnsafeAttribute($name, $value)
    {
        // ignored (too much records in debug)
    }

    /**
     * Get required attribute names and uncheck them from attributes list
     *
     * @param array $attributes
     *
     * @return string[] attribute names
     */
    private function uncheckRequiredAttributes(array $attributes): array
    {
        $this->_required = [];

        foreach ($attributes as $i => $attribute) {
            if ($attribute[0] === '*') {
                $attributes[$i] = substr($attribute, 1);
                $this->_required[] = $attributes[$i];
            }
        }

        return $attributes;
    }

    /**
     * Adds a set of fields to the set of fields assigned for the current instance
     *
     * @param string $name
     */
    protected function addToUsedAttributes(string $name): void
    {
        if (!in_array($name, $this->_usedAttributes)) {
            $this->_usedAttributes[] = $name;
        }
    }

    /** @inheritdoc */
    public function fields(): array
    {
        return $this->filterNullFields(parent::fields());
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    protected function filterNullFields(array $fields = [])
    {
        if (empty($fields)) {
            $fields = $this->attributes();
        }

        if (!$this->exportNullFields) {
            foreach ($fields as $field) {
                if ($this->$field === null) {
                    unset($fields[$field]);
                }
            }
        }

        return $fields;
    }

    /**
     * Returns the validation rules for attributes
     *
     * @return array
     */
    protected function getValidationRules(): array
    {
        $validationRules = $this->rules();
        $required = $this->getRequiredAttributes();
        if (!empty($required)) {
            $validationRules[] = [$required, 'required'];
        }

        return $validationRules;
    }

    /**
     * Creates validator objects based on the validation rules specified in [[rules()]].
     * Unlike [[getValidators()]], each time this method is called, a new list of validators will be returned.
     * @return ArrayObject validators
     * @throws InvalidConfigException if any validation rule configuration is invalid
     */
    public function createValidators()
    {
        Validator::setValidators();
        $validators = new ArrayObject();

        foreach ($this->getValidationRules() as $rule) {
            if ($rule instanceof \yii\validators\Validator) {
                $validators->append($rule);
            } elseif (is_array($rule) && isset($rule[0], $rule[1])) { // attributes, validator type
                /** @var Model|ActiveRecord $this */
                $validator = Validator::createValidator($rule[1], $this, (array)$rule[0], array_slice($rule, 2));
                $validators->append($validator);
            } else {
                throw new InvalidConfigException('Invalid validation rule: a rule must specify both attribute names and validator type.');
            }
        }

        return $validators;
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function scenarios()
    {
        /** @var Model|ActiveRecord $this */
        throw new Exception('scenarios() method not set in the model ' . self::class);
        //return [
        //	self::SCENARIO_DEFAULT => $this->attributes(),
        //];
    }

    /**
     * Add uuid attributes to array of attributes
     *
     * @param array $attributes Attribute list
     *
     * @return array
     */
    protected function addUuidAttributes(array $attributes): array
    {
        $result = $attributes;
        $this->_addedAttributes = [];

        foreach ($attributes as $attribute) {
            if ($this instanceof ActiveRecord) {
                $find = '_id';
                $replace = '_uuid';
            } else {
                $find = '_uuid';
                $replace = '_id';
            }
            $attr = str_replace($find, $replace, $attribute);
            if (strpos($attribute, $find) && !in_array($attr, $attributes) && $this->hasMethod('get' . $attr)) {
                $result[] = $attr;
                $this->_addedAttributes[] = $attr;
            }
        }

        return $result;
    }

    /**
     * Get required attribute names
     *
     * @return string[] Required attribute names
     */
    public function getRequiredAttributes(): array
    {
        /** @var Model|ActiveRecord $this */
        $this->activeAttributes();

        return $this->_required;
    }

    /**
     * Returns the attribute names that are subject to validation in the current scenario.
     *
     * @return string[] safe attribute names
     */
    public function activeAttributes(): array
    {
        $attributes = parent::activeAttributes();

        return $this->uncheckRequiredAttributes($attributes);
    }

    /**
     * Returns attribute values on the current or transferred scenario
     *
     * @param string $scenario
     * @param bool $exceptMagic
     *
     * @return array
     */
    public function getAttributesOnScenario(string $scenario = null, bool $exceptMagic = true): array
    {
        /** @var Model|ActiveRecord $this */
        if (!is_null($scenario)) {
            $this->setScenario($scenario);
        }

        return $this->getAttributes($this->activeAttributes(), [], $exceptMagic);
    }

    /**
     * Returns a list of all attributes with values that were massively assigned for this instance
     * @return array ['attribute_name'=>'value', 'attribute_name1'=>'value1', ...]
     */
    public function getUsedAttributes(): array
    {
        /** @var Model|ActiveRecord $this */
        return $this->getAttributes($this->_usedAttributes);
    }

    /**
     * Returns a list of all attributes that were massively assigned for this instance
     * @return array ['attribute_name1', 'attribute_name2', ...]
     */
    public function usedAttributes(): array
    {
        return $this->_usedAttributes;
    }

    /**
     * Unset attributes of model
     *
     * @param array|null $names Array of names of attributes
     */
    public function unsetAttributes(array $names = null): void
    {
        /** @var Model|ActiveRecord $this */
        if (is_null($names)) {
            $names = $this->attributes();
        }
        foreach ($names as $name) {
            if (!$this->canSetProperty($name)) {
                continue;
            }
            if ($this instanceof ActiveRecord) {
                unset($this->$name);
            } else {
                $this->$name = null;
            }
        }
    }

    /**
     * Wraps standard validate() method with ModelValidationException in case of false.
     *
     * @return void
     * @throws ModelValidationException
     */
    public function validateOrDie(): void
    {
        if (!$this->validate()) {
            /** @var Model|ActiveRecord $this */
            throw new ModelValidationException($this);
        }
    }

    /**
     * @return bool
     */
    public function isExportNullFields(): bool
    {
        return $this->exportNullFields;
    }

    /**
     * @param bool $exportNullFields
     */
    public function setExportNullFields(bool $exportNullFields)
    {
        $this->exportNullFields = $exportNullFields;
    }

    /** @inheritdoc */
    public function jsonSerialize(): array
    {
        return $this->getAttributes($this->fields());
    }
}
