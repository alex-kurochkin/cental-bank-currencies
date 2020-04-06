<?php
declare(strict_types=1);

namespace common\modules\CentroBank\api;

use common\modules\CentroBank\parsers\CentralBankApiRateXMLParser;

class CentralBankApi /* implements ValuteApi... not now, we have no exhaustive information */
{

    private string $url = 'http://www.cbr.ru/scripts/XML_daily.asp?date_req=';

    public function getRateByDate(string $date): array
    {
        $xmlData = file_get_contents($this->url . $date);

        $rateParser = new CentralBankApiRateXMLParser();
        $rateParser->load($xmlData);

        $rates = [];

        foreach ($rateParser->getOne() as $rate) {
            $rates[] = $rate;
        }

        return $rates;
    }
}
