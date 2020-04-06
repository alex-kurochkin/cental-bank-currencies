<?php

namespace common\components;

use Yii;
use yii\base\InvalidConfigException;

/**
 * Class Repository
 */
class Repository
{

    /** @var Connection */
    protected $db;

    /** @var ModelMapper */
    protected $model;
    /** @var ActiveRecord */
    protected $modelAr;

    /**
     * Repository constructor
     */
    public function __construct()
    {
        $this->db = Yii::$app->db;
    }

    /**
     * @param string|null $class
     * @return static
     * @throws InvalidConfigException
     */
    public static function getInstance(string $class = null): self
    {
        return Yii::createObject($class ?? static::class);
    }

    /**
     * Find one record by id
     *
     * @param int $id Record id
     * @return ModelMapper|null
     * @throws InvalidConfigException
     */
    public function findOneById(int $id): ?ModelMapper
    {
        $data = $this->modelAr::find()
            ->where(['id' => $id])
            ->asArray()
            ->one();

        return $this->fillModel($data);
    }

    /**
     * @param $data
     * @param bool $isMany
     * @return array|object|null
     * @throws InvalidConfigException
     */
    protected function fillModel($data, bool $isMany = false)
    {
        if (!$isMany) {
            if ($data === null) {
                return null;
            }

            return Yii::createObject($this->model, [$data]);
        }

        if (is_array($data) && empty($data)) {
            return [];
        }

        $models = [];
        foreach ($data as $item) {
            $models[] = Yii::createObject($this->model, [$item]);
        }

        return $models;
    }
}
