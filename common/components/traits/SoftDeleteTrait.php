<?php

namespace common\components\traits;

use Yii;
use yii\web\BadRequestHttpException;

/**
 * Soft delete behavior
 */
trait SoftDeleteTrait
{
    /** @var bool Whether soft deleting is necessary */
    public static $softDelete = true;
    /** @var string Soft delete attribute */
    protected static $softDeleteAttribute = 'deleted';

    /**
     * @return string
     */
    public static function getSoftDeleteAttribute(): string
    {
        return self::$softDeleteAttribute;
    }

    /**
     * Whether it is necessary and whether soft deleting is possible
     *
     * @return bool
     */
    public static function isSoftDelete(): bool
    {
        return static::$softDelete && static::checkField(static::$softDeleteAttribute, false);
    }

    /** @inheritdoc */
    public function beforeDelete(): bool
    {
        if (!static::isSoftDelete()) {// Do nothing if safe mode is disabled. This will result in a normal deletion
            return parent::beforeDelete();
        } else { // Remove and return false to prevent real deletion
            $this->softDelete();

            return false;
        }
    }

    /**
     * Restore soft-deleted record
     */
    public function restore(): void
    {
        $attribute = static::$softDeleteAttribute;
        $this->$attribute = 0;
        $this->safeUpdate([$attribute]);
    }

    /**
     * Delete record from database regardless of the $softDelete attribute
     */
    public function forceDelete()
    {
        $softDelete = static::$softDelete;
        static::$softDelete = false;
        $this->delete();
        static::$softDelete = $softDelete;
    }

    /**
     * Soft deleted record
     *
     * @param array $relations
     * @return int Amount of deleted rows
     * @throws BadRequestHttpException
     */
    public function softDelete(array $relations = [])
    {
        static::checkField(static::$softDeleteAttribute);

        if ($this->{static::$softDeleteAttribute}) {
            return 1;
        }

        $pk = static::primaryKeyFirst();

        return static::softDeleteAll([$pk => $this->{$pk}], $relations);
    }

    /**
     * Soft deleted records
     *
     * @param array $condition the conditions that will be put in the WHERE part of the DELETE SQL.
     * @param array $relations
     *
     * @return int Amount of deleted rows
     * @throws BadRequestHttpException
     */
    public static function softDeleteAll(array $condition, array $relations = [])
    {
        static::checkField(static::$softDeleteAttribute);

        if (empty($condition)) {
            throw new BadRequestHttpException('Deleting without conditions isn\'t allowed');
        }

        if (static::checkField('user_id', false) && !isset($condition['user_id'])) {
            $condition['user_id'] = Yii::$app->user->id;
        }

        if (empty($relations)) {
            return static::updateAll([static::$softDeleteAttribute => 1], $condition);
        }

        $models = static::find()
            ->with($relations)
            ->where($condition)
            ->indexBy(static::primaryKeyFirst())
            ->all();

        /**
         * @param array $relations
         *
         * @return static[]
         */
        $getIds = function (array $relations) use (&$getIds) {
            $ids = [];
            foreach ($relations as $item) {
                if (is_array($item)) {
                    $ids = array_merge($ids, $getIds($item));
                } else {
                    /** @var static $item */
                    $ids[get_class($item)][] = $item->getPrimaryKey();
                    $ids = array_merge($ids, $getIds($item->getRelatedRecords()));
                }
            }

            return $ids;
        };

        $ids = $getIds($models);

        $count = 0;
        /** @var static $class */
        foreach ($ids as $class => $id) {
            $pk = $class::primaryKeyFirst();
            $count += $class::updateAll([static::$softDeleteAttribute => 1], [$pk => $id]);
        }

        return $count;
    }
}
