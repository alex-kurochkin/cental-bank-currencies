<?php

namespace common\domain\persistence;

use common\domain\models\Preparable;
use common\domain\persistence\exceptions\EntityNotFoundException;
use common\domain\persistence\exceptions\ModifyPersistenceException;
use common\domain\utils\ErrorMessageBuilder;
use Closure;
use Throwable;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

abstract class BaseRepository
{

    /** @var ActiveRecordMapper */
    private $mapper;

    public function __construct(string $activeRecordType, array $mapping, string $modelType, array $converters = [])
    {
        $this->mapper = new ActiveRecordMapper($activeRecordType, $mapping, $modelType, $converters);
    }

    /**
     * @return ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    protected function getQuery(): ActiveQuery
    {
        return Yii::createObject(ActiveQuery::class, [$this->getActiveRecordType()]);
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function findAll()
    {
        $ars = $this->getQuery()->all();
        return $this->createManyModels($ars);
    }

    /**
     * @return int
     * @throws \yii\base\InvalidConfigException
     */
    public function countAll()
    {
        return $this->getQuery()->count();
    }

    /**
     * @param Closure $builder
     * @return int
     * @throws \yii\base\InvalidConfigException
     */
    public function countMany(Closure $builder): int
    {
        $query = $this->getQuery();
        $builder($query);

        return $query->count();
    }

    /**
     * @param Closure $builder
     * @param int $offset
     * @param int $limit
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function findMany(Closure $builder, int $offset = -1, int $limit = -1)
    {
        $query = $this->getQuery();
        $builder($query);

        $ars = $query->offset($offset)->limit($limit)->all();
        return $this->createManyModels($ars);
    }

    /**
     * @param Closure $builder
     * @return mixed
     * @throws EntityNotFoundException
     * @throws \yii\base\InvalidConfigException
     */
    public function findOne(Closure $builder)
    {
        $model = $this->findOneOrNull($builder);
        if ($model == null) {
            throw new EntityNotFoundException($this->getModelType());
        }

        return $model;
    }

    /**
     * @param Closure $builder
     * @return mixed|null
     * @throws \yii\base\InvalidConfigException
     */
    public function findOneOrNull(Closure $builder)
    {
        $query = $this->getQuery();
        $builder($query);

        return $this->createOneModel($query->one());
    }

    public function createOne($model)
    {
        $ar = $this->createActiveRecord($model);
        if (!$ar->save()) {
            $message = ErrorMessageBuilder::build($ar->errors);
            throw new ModifyPersistenceException($message, $model);
        }

        return $this->createOneModel($ar);
    }

    public function updateOne($model)
    {
        try {
            $ar = $this->createActiveRecord($model);
            if (!$ar->update() && !empty($ar->errors)) {
                $message = ErrorMessageBuilder::build($ar->errors);
                throw new ModifyPersistenceException($message, $model);
            }

            return $model;

        } catch (ModifyPersistenceException $e) {
            throw $e;

        } catch (Throwable $e) {
            throw new ModifyPersistenceException($e->getMessage(), $model, $e);
        }
    }

    public function deleteMany(array $models)
    {
        foreach ($models as $model) {
            $this->deleteOne($model);
        }

        return $models;
    }

    public function deleteOne($model)
    {
        try {
            $ar = $this->createActiveRecord($model);
            $ar->delete();

            return $model;

        } catch (Throwable $e) {
            throw new ModifyPersistenceException($e->getMessage(), $model, $e);
        }
    }

    protected function createActiveRecord($model)
    {
        return $this->mapper->toActiveRecord($model);
    }

    protected function createManyModels(array $ars)
    {
        return $this->mapper->toManyModels($ars);
    }

    protected function createOneModel(?ActiveRecord $ar)
    {
        $model = $this->mapper->toOneModel($ar);
        if ($model instanceof Preparable) {
            $model->prepare();
        }

        return $model;
    }

    protected function getModelType(): string
    {
        return $this->mapper->externalType;
    }

    protected function getActiveRecordType(): string
    {
        return $this->mapper->internalType;
    }
}
