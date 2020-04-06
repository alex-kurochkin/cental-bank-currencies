<?php

namespace common\domain\persistence;

use common\domain\mappers\ObjectMapper;
use common\domain\persistence\converters\IntToBooleanConverter;
use common\domain\persistence\converters\LowerToUpperCaseConverter;
use common\domain\persistence\converters\StringToDateConverter;
use common\domain\persistence\converters\StringToDateTimeConverter;
use common\domain\persistence\converters\JsonToValueConverter;
use common\domain\persistence\converters\StringToIntConverter;
use common\domain\persistence\converters\UpperToLowerCaseConverter;
use yii\db\ActiveRecord;

class ActiveRecordMapper extends ObjectMapper
{

    public static $converters = [
        // short alias
        'bool' => IntToBooleanConverter::class,
        'date' => StringToDateConverter::class,
        'datetime' => StringToDateTimeConverter::class,
        'json' => JsonToValueConverter::class,
        'int' => StringToIntConverter::class,

        // as type
        IntToBooleanConverter::class,
        StringToIntConverter::class,
        StringToDateConverter::class,
        StringToDateTimeConverter::class,
        JsonToValueConverter::class,
        LowerToUpperCaseConverter::class,
        UpperToLowerCaseConverter::class,
    ];

    public function __construct(string $activeRecordType, array $mapping, string $modelType, array $converters = [])
    {
        parent::__construct($activeRecordType, $mapping, $modelType, array_merge(self::$converters, $converters));
    }

    /**
     * @param ActiveRecord[] $activeRecords
     * @return array
     */
    public function toManyModels(array $activeRecords)
    {
        return parent::toManyExternals($activeRecords);
    }

    /**
     * @param ActiveRecord|null $activeRecord
     * @param mixed             $model
     * @return mixed|null
     */
    public function toOneModel($activeRecord, $model = null)
    {
        return parent::toOneExternal($activeRecord, $model);
    }

    /**
     * @param mixed|null   $model
     * @param ActiveRecord $activeRecord
     * @return ActiveRecord|null
     */
    public function toActiveRecord($model, $activeRecord = null)
    {
        return parent::toOneInternal($model, $activeRecord);
    }
}
