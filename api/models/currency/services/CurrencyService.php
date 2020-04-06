<?php
declare(strict_types=1);

namespace api\models\currency\services;

use api\models\currency\Currency;
use api\models\currency\repositories\CurrencyRepository;

class CurrencyService
{

    private CurrencyRepository $currencyRepository;

    public function __construct(CurrencyRepository $currencyRepository)
    {
        $this->currencyRepository = $currencyRepository;
    }

    /**
     * @param \DateTimeInterface $from
     * @param \DateTimeInterface $to
     * @return Currency[]
     * @throws \yii\base\InvalidConfigException
     */
    public function findManyRatesByDateFromAndTo(\DateTimeInterface $from, \DateTimeInterface $to): array
    {
        return $this->currencyRepository->findManyRatesByDateFromAndTo($from, $to);
    }

    /**
     * @param \DateTimeInterface $date
     * @param \stdClass[] $dayCurrencies
     * @throws \ErrorException
     */
    public function createMany(\DateTimeInterface $date, array $dayCurrencies): void
    {
        $currencies = [];

        foreach ($dayCurrencies as $currency) {
            $c = new Currency();

            $c->id = 0;
            $c->valuteId = $currency->valuteId;
            $c->numCode = $currency->numCode;
            $c->charCode = $currency->charCode;
            $c->name = $currency->name;
            $c->nominal = $currency->nominal;
            $c->value = $currency->value;
            $c->date = $date;

            $currencies[] = $c;
        }

        $this->currencyRepository->createMany($currencies);
    }
}
