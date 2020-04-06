<?php
declare(strict_types=1);

namespace console\controllers;

use api\models\currency\services\CurrencyService;
use common\modules\CentroBank\api\CentralBankApi;
use yii\console\Controller;

class CentrobankController extends Controller
{

    private string $bankDateFormat = 'd/m/Y';

    private int $daysCount = 30;

    private CurrencyService $currencyService;

    public function __construct($id, $module, CurrencyService $currencyService, $config = [])
    {
        $this->currencyService = $currencyService;
        parent::__construct($id, $module, $config);
    }

    public function actionLoad(): void
    {
        $date = (new \DateTimeImmutable())->setTime(0,0);

        $bankApi = new CentralBankApi();

        $i = 0;
        while (++$i <= $this->daysCount) {

            $rates = $bankApi->getRateByDate($date->format($this->bankDateFormat));

            try {
                $this->currencyService->createMany($date, $rates);
            } catch (\ErrorException $e) {
                printf('%s() for %s caught ErrorException: %s'. PHP_EOL,
                    __METHOD__,
                    $date->format('Y-m-d'),
                    $e->getMessage()
                );
            }

            $date = $date->modify('-1 day');
        }
    }
}
