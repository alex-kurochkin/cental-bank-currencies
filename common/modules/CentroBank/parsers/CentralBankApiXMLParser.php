<?php
declare(strict_types=1);

namespace common\modules\CentroBank\parsers;

/**
 * Class CBankApiXMLParser
 * @package common\models\CentroBank\parsers
 */
abstract class CentralBankApiXMLParser
{

    protected string $xmlData;
    protected \SimpleXMLElement $xml;

    /**
     * @param string $xmlData
     */
    public function load(string $xmlData)
    {
        $this->xmlData = $xmlData;
        $this->xml = simplexml_load_string($xmlData);
    }

    public abstract function getAttributes(): \stdClass;
    public abstract function getOne(): \Generator;
}
