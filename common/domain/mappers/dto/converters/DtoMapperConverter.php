<?php

namespace common\domain\mappers\dto\converters;

use common\domain\mappers\dto\DtoMapper;
use common\domain\mappers\ValueConverter;

class DtoMapperConverter implements ValueConverter
{

    /** @var DtoMapper */
    private $mapper;

    public function __construct(DtoMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function toExternal($dto)
    {
        if ($dto == null) {
            return null;
        }

        return is_array($dto)
            ? $this->mapper->toManyModels($dto)
            : $this->mapper->toOneModel($dto);
    }

    public function toInternal($model)
    {
        if ($model == null) {
            return null;
        }

        return is_array($model)
            ? $this->mapper->toManyDtos($model)
            : $this->mapper->toOneDto($model);
    }
}
