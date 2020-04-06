<?php

namespace common\components;

use common\components\traits\ModelTrait;
use JsonSerializable;

/**
 * Class Model
 *
 * @property array $attributesOnRequest
 */
class Model extends \yii\base\Model implements JsonSerializable
{

    use ModelTrait;

    /** @var array cache list of attribute names */
    protected $_attributes = [];

    /**
     * Declares event handlers for the [[owner]]'s events.
     *
     * Child classes may override this method to declare what PHP callbacks should
     * be attached to the events of the [[owner]] component.
     *
     * The callbacks will be attached to the [[owner]]'s events when the behavior is
     * attached to the owner; and they will be detached from the events when
     * the behavior is detached from the component.
     *
     * The callbacks can be any of the following:
     *
     * - method in this behavior: `'handleClick'`, equivalent to `[$this, 'handleClick']`
     * - object method: `[$object, 'handleClick']`
     * - static method: `['Page', 'handleClick']`
     * - anonymous function: `function ($event) { ... }`
     *
     * The following is an example:
     *
     * ```php
     * [
     *     Model::EVENT_BEFORE_VALIDATE => 'myBeforeValidate',
     *     Model::EVENT_AFTER_VALIDATE => 'myAfterValidate',
     * ]
     * ```
     *
     * @return array events (array keys) and the corresponding event handler methods (array values).
     */
    public function events(): array
    {
        return [
            Model::EVENT_BEFORE_VALIDATE => function () { //trim attributes
                foreach ($this->attributes as $name => $value) {
                    if (is_string($value) && !empty($value) && trim($value) != $value) {
                        $this->setAttribute($name, trim($value));
                    }
                }
            },
        ];
    }

    /** @inheritdoc */
    public function init()
    {
        parent::init();

        foreach ($this->events() as $event => $handler) {
            $this->on($event, is_string($handler) ? [$this, $handler] : $handler);
        }
    }

    /**
     * Set and get attributes on scenario
     *
     * @param array $data
     * @param string|null $scenarioForSet
     * @param string|null $scenarioForGet
     *
     * @return array
     */
    public function setGet(array $data, string $scenarioForSet = null, string $scenarioForGet = null): array
    {
        $this->unsetAttributes($this->usedAttributes());
        if ($scenarioForSet !== null) {
            $this->setScenario($scenarioForGet);
        }
        $this->setAttributes($data);

        return $this->getAttributesOnScenario($scenarioForGet);
    }

    /** @inheritdoc */
    public function attributes(): array
    {
        if ($this->_attributes === []) {
            $this->_attributes = $this->addUuidAttributes(parent::attributes());
        }

        return $this->_attributes;
    }

    /** @inheritdoc */
    public function getAttributes($names = null, $except = [], bool $exceptMagic = false): array
    {
        $values = [];
        if ($names === null) {
            $names = $this->attributes();
        } elseif (!$exceptMagic) {
            $names = $this->addUuidAttributes($names);
        }
        if ($exceptMagic) {
            $except = $this->_addedAttributes;
        }
        $names = array_diff($names, $except);

        foreach ($names as $name) {
            $values[$name] = $this->$name;
        }

        return $values;
    }
}
