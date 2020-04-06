<?php

namespace common\components;

use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;

/**
 * Class Service
 */
abstract class Service extends BaseObject
{

    /** @var string Repository class */
    protected $repository;

    /**
     * @param string|null $repositoryClass
     * @return Repository
     * @throws InvalidConfigException
     */
    protected function getRepository(string $repositoryClass = null): Repository
    {
        return Yii::createObject($repositoryClass ?? $this->repository);
    }
}
