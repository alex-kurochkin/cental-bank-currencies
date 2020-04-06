<?php

namespace common\components;

use yii\base\BaseObject;
use yii\base\UnknownPropertyException;

/**
 * Class ServiceRoute
 * @property array $services
 */
class ServiceRoute extends BaseObject
{
    /** @var array of services (['name' => 'classNameSpace']) */
    protected $services;

    /** @inheritdoc */
    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            foreach ($config as $name => $value) {
                $this->$name = $value;
            }
        }
        parent::__construct();
    }

    /** @inheritdoc */
    public function __get($name)
    {
        return $this->getService($name);
    }

    /**
     * Get an instance of service by name
     *
     * @param string $name Service name
     *
     * @return Service Instance of service
     * @throws UnknownPropertyException
     */
    public function getService(string $name)
    {
        if (!isset($this->services[$name])) {
            throw new UnknownPropertyException("Service \"$name\" not found");
        }

        if (is_string($this->services[$name])) {
            $this->services[$name] = new $this->services[$name]();
        }

        return $this->services[$name];
    }

    /**
     * @return array
     */
    public function getServices(): array
    {
        return $this->services;
    }
}
