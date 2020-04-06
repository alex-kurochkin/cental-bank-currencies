<?php

namespace common\components;

use common\components\behaviors\TimestampFieldBehavior;
use common\components\exceptions\Exception;
use common\components\exceptions\ModelValidationException;
use common\components\traits\CacheTrait;
use common\components\traits\ModelTrait;
use common\components\traits\SoftDeleteTrait;
use JsonSerializable;
use UnexpectedValueException;
use Yii;
use yii\base\InvalidValueException;
use yii\base\UnknownPropertyException;
use yii\helpers\ArrayHelper;

/**
 * Class ActiveRecord
 */
class ActiveRecord extends \yii\db\ActiveRecord implements JsonSerializable
{

    use ModelTrait, SoftDeleteTrait, CacheTrait;

    /** @inheritdoc */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        if ($this->hasAttribute('create_time')) {
            $behaviors[] = [
                'class' => TimestampFieldBehavior::class,
                'fields' => ['create_time'],
            ];
        }

        return $behaviors;
    }

    /** @inheritdoc */
    public function __set($name, $value)
    {
        parent::__set($name, $value);
        $this->addToUsedAttributes($name);
    }

    /**
     * @inheritdoc
     * @return ActiveQuery
     */
    public static function find(): ActiveQuery
    {
        return new ActiveQuery(static::class);
    }

    /**
     * Creates and saves new record with the supplied data
     *
     * @param array $data
     * @param string $scenario
     * @param bool $runValidation
     * @param array $attributes
     *
     * @return static Created record
     */
    public static function createWith(array $data, string $scenario = self::SCENARIO_DEFAULT, bool $runValidation = false, array $attributes = null): self
    {
        $model = new static();
        $model->setScenario($scenario);
        $model->setAttributes($data);
        $model->safeInsert($runValidation, $attributes);

        return $model;
    }

    /**
     * Determines whether the record with given primary key exists.
     * Method works only for table with a single primary key
     *
     * @param int|string $value Value of the primary key to look for
     *
     * @return bool
     */
    public static function existsByPK($value): bool
    {
        $primaryKeys = static::primaryKey();
        if (count($primaryKeys) !== 1) {
            throw new UnexpectedValueException('This method can not work with tables that have more than one primary key');
        }

        return static::find()
            ->where([$primaryKeys[0] => $value])
            ->exists();
    }

    /**
     * Find one record by primary key
     *
     * @param int $id
     *
     * @return static|null
     */
    public static function findOneByPK(int $id): ?self
    {
        $primaryKey = static::primaryKeyFirst();

        return static::find()
            ->where([$primaryKey => $id])
            ->one();
    }

    /**
     * Updates this record with the supplied data
     *
     * @param array $data New values
     * @param string $scenario
     * @param bool $runValidation
     * @param array $attributes
     *
     * @return static
     */
    public function updateWith(array $data, string $scenario = self::SCENARIO_DEFAULT, bool $runValidation = false, array $attributes = null): self
    {
        $this->setScenario($scenario);
        $this->setAttributes($data);
        $this->safeUpdate($attributes, $runValidation);

        return $this;
    }

    /**
     * Updates records with the specified primary key.
     * Note, the attributes are not checked for safety and validation is NOT performed.
     *
     * @param mixed $pk primary key value(s). Use array for multiple primary keys. For composite key, each key value must be an array (column
     *                          name=>column value).
     * @param array $attributes list of attributes (name=>$value) to be updated
     *
     * @return integer the number of rows being updated
     */
    public static function updateByPK($pk, array $attributes): int
    {
        $primaryKey = static::primaryKeyFirst();
        if (empty($primaryKey)) {
            throw new UnexpectedValueException('This method can not work with tables that have more than one primary key');
        }

        return static::updateAll($attributes, [$primaryKey => $pk]);
    }

    /**
     * Use [[insert]] or [[update]]
     * @inheritdoc
     * @throws Exception
     */
//	public function save($runValidation = true, $attributeNames = null)
//	{
//		throw new Exception('Use appropriate insert or update methods');
//	}

    /**
     * Will only return true or throw exception, removing the need to check for result of operation
     *
     * For description of insert see [[\yii\db\ActiveRecord::insert]]
     *
     * @param bool $runValidation whether to perform validation before saving the record.
     *                             If the validation fails, the record will not be inserted into the database.
     * @param array $attributes list of attributes that need to be saved. Defaults to null,
     *                             meaning all attributes that are loaded from DB will be saved.
     *
     * @return bool true if and only if validated and inserted successfully
     * @throws Exception If failed to save to database
     * @throws ModelValidationException If external data failed to validate
     */
    public function safeInsert(bool $runValidation = false, array $attributes = null): bool
    {
        if ($runValidation) {
            $valid = $this->validate($attributes);
            if ($valid === false) {
                throw new ModelValidationException($this);
            }
        }
        $result = $this->insert(false, $attributes);
        if ($result === true) {
            return true;
        } else {
            throw new Exception('Failed inserting row');
        }
    }

    /**
     * Will only return true or throw exception, removing the need to check for result of operation
     *
     * For description of update see [[\yii\db\ActiveRecord::update]]
     *
     * @param bool $runValidation whether to perform validation before saving the record.
     *                              If the validation fails, the record will not be updated
     * @param array $attributeNames list of attributes that need to be saved. Defaults to null,
     *                              meaning all attributes that are loaded from DB will be saved.
     *
     * @return int Amount of updated rows
     * @throws Exception If failed to save to database
     * @throws ModelValidationException If external data failed to validate
     */
    public function safeUpdate(array $attributeNames = null, bool $runValidation = false)
    {
        if ($runValidation) {
            $valid = $this->validate($attributeNames);
            if ($valid === false) {
                throw new ModelValidationException($this);
            }
        }
        $result = $this->update(false, $attributeNames);
        if ($result === false) {
            throw new Exception('Failed updating row');
        } else {
            return $result;
        }
    }

    /**
     * Returns the list of all attribute names of the model.
     * The default implementation will return all column names of the table associated with this AR class.
     *
     * @param bool $valueAsKey
     *
     * @return array list of attribute names.
     */
    public static function attributesModel($valueAsKey = false)
    {
        $attributes = array_keys(static::getTableSchema()->columns);

        return $valueAsKey ? array_flip($attributes) : $attributes;
    }

    /**
     * Returns the primary key name(s) for this AR class.
     *
     * @return string the primary key of the associated database table.
     */
    public static function primaryKeyFirst()
    {
        return parent::primaryKey()[0];
    }

    /**
     * Insert of a models batch
     *
     * @param static[]|array $data Array of models or attributes of models
     * @param array $columns
     *
     * @return integer number of rows affected by the execution.
     */
    public static function insertMultiple(array $data, array $columns = []): int
    {
        $firstElement = reset($data);
        $isActiveRecords = false;

        if ($firstElement instanceof static) {
            // called event beforeSave
            foreach ($data as $model) {
                $model->beforeSave(true);
            }
            $rows = ArrayHelper::getColumn($data, 'usedAttributes');
            $isActiveRecords = true;
        } elseif ($firstElement instanceof self) {
            throw new InvalidValueException('$data MUST be array of a current model or attributes of models');
        } else {
            $rows = $data;
        }

        if (empty($columns)) {
            foreach ($data as $item) {
                $columns = array_merge($columns, !$isActiveRecords ? array_keys($item) : $item->usedAttributes());
            }
            $columns = array_unique($columns);
        }

        if (empty($columns)) {
            return 0; // TODO: mb exception?
        }

        $rows = array_map(function ($row) use ($columns) {
            $result = [];
            foreach ($columns as $column) {
                $result[$column] = $row[$column] ?? null;
            }

            return $result;
        }, $rows);

        $result = Yii::$app->db->createCommand()
            ->batchInsert(static::tableName(), $columns, $rows)
            ->execute();

        if ($isActiveRecords) {
            // called event afterSave
            foreach ($data as $model) {
                $model->afterSave(true, $columns);
            }
        }

        return $result;
    }

    /**
     * Check a field and die if it doesn't exist
     *
     * @param string $fieldName Field name for checking
     * @param bool $showError Whether it is necessary to give an error message
     *
     * @return bool
     * @throws UnknownPropertyException
     */
    public static function checkField(string $fieldName, bool $showError = true)
    {
        $isset = in_array($fieldName, static::attributesModel(), true);

        if ($showError && !$isset) {
            throw new UnknownPropertyException(Module::t("Class {class} doesn't contain the \"{fieldName}\" field", [
                '{class}' => static::class,
                '{fieldName}' => $fieldName,
            ]));
        }

        return $isset;
    }

}
