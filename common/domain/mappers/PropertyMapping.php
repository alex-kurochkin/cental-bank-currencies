<?php

namespace common\domain\mappers;

class PropertyMapping
{
    /** @var string */
    public $internalName;

    /** @var string */
    public $externalName;

    /** @var bool */
    public $externalReadOnly;

    /** @var ValueConverter */
    public $externalConverter;

    public function __construct(string $internalName, string $externalName, bool $externalReadOnly, ?ValueConverter $externalConverter = null)
    {
        $this->internalName = $internalName;
        $this->externalName = $externalName;
        $this->externalReadOnly = $externalReadOnly;
        $this->externalConverter = $externalConverter;
    }
}
