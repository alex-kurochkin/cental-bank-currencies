<?php

namespace common\domain\mappers\dto;

use common\domain\mappers\dto\converters\IntToBooleanConverter;
use common\domain\mappers\dto\converters\StringToDateConverter;
use common\domain\mappers\dto\converters\StringToDateTimeConverter;
use common\domain\mappers\ObjectMapper;
use yii\base\BaseObject;

class DtoMapper extends ObjectMapper
{

    public static $converters = [
        // short alias
        'date' => StringToDateConverter::class,
        'datetime' => StringToDateTimeConverter::class,
        'bool' => IntToBooleanConverter::class,

        // types
        StringToDateConverter::class,
        StringToDateTimeConverter::class,
        IntToBooleanConverter::class,
    ];

    public function __construct(string $dtoType, array $mapping, string $modelType, array $converters = [])
    {
        parent::__construct($dtoType, $mapping, $modelType, array_merge(self::$converters, $converters));
    }

    /**
     * @param array $models
     * @return array
     */
    public function toManyDtos(array $models)
    {
        return parent::toManyInternals($models);
    }

    /**
     * @param mixed|null $model
     * @param BaseObject $dto
     * @return BaseObject|null
     */
    public function toOneDto($model, $dto = null)
    {
        return parent::toOneInternal($model, $dto);
    }

    /**
     * @param array $dtos
     * @return array
     */
    public function toManyModels(array $dtos)
    {
        return parent::toManyExternals($dtos);
    }

    /**
     * @param BaseObject|null $dto
     * @param mixed           $model
     * @return mixed|null
     */
    public function toOneModel($dto, $model = null)
    {
        return parent::toOneExternal($dto, $model);
    }
}
