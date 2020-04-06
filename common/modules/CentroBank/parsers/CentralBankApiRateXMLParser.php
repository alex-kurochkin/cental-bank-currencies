<?php
declare(strict_types=1);

namespace common\modules\CentroBank\parsers;

/**
 * Class CBankApiRateXMLParser
 * @package common\models\CentroBank\parsers
 */
class CentralBankApiRateXMLParser extends CentralBankApiXMLParser
{

    /**
     * @return \stdClass
     */
    public function getAttributes(): \stdClass
    {
        $data = $this->xml->attributes();

        $attributes = [];

        foreach ($data as $value) { // Date, name
            $attributes[$value->getName()] = (string)$value;
        }

        return (object)$attributes;
    }

    /**
     * @return \Generator
     */
    public function getOne(): \Generator
    {
        foreach ($this->xml as $currencyValue) {

            yield (object)[
                'valuteId' => (string)$currencyValue->attributes(),
                'numCode' => (int)$currencyValue->NumCode,
                'charCode' => (string)$currencyValue->CharCode,
                'nominal' => (int)$currencyValue->Nominal,
                'name' => (string)$currencyValue->Name,
                'value' => (float)str_replace(',', '.', $currencyValue->Value),
            ];
        }
    }
}
