<?php

namespace common\components;

/**
 * Class Repository
 */
class RepositoryDictionary extends Repository
{

    /** @var ModelMapper[] */
    protected static $_cache = [];

    /**
     * Find  by id
     *
     * @param int $id User id
     *
     * @return ModelMapper|null
     */
    public function findOneById(int $id): ?ModelMapper
    {
        return $this->getAll()[$id];
    }

    /**
     *
     * @return array
     */
    public function getAll(): array
    {
        if (empty(static::$_cache)) {
            $records = $this->modelAr::find()
                ->asArray()
                ->cache()
                ->all();

            foreach ($records as $record) {
                static::$_cache[] = new $this->model($record);
            }
        }

        return static::$_cache;
    }
}
