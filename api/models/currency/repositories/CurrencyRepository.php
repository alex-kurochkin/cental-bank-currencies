<?php
declare(strict_types=1);

namespace api\models\currency\repositories;

use api\models\currency\Currency;
use api\models\currency\repositories\ars\CurrencyAr;
use common\domain\persistence\BaseRepository;
use yii\db\ActiveQuery;
use yii\db\IntegrityException;

class CurrencyRepository extends BaseRepository
{

    public function __construct()
    {
        parent::__construct(CurrencyAr::class, CurrencyAr::MAPPING, Currency::class);
    }

    /**
     * @param \DateTimeInterface $from
     * @param \DateTimeInterface $to
     * @return Currency[]
     * @throws \yii\base\InvalidConfigException
     */
    public function findManyRatesByDateFromAndTo(\DateTimeInterface $from, \DateTimeInterface $to): array
    {
        return $this->findMany(static function (ActiveQuery $query) use ($from, $to) {
            $query
                ->where(['>=', CurrencyAr::DATE, $from->format('Y-m-d')])
                ->andWhere(['<=', CurrencyAr::DATE, $to->format('Y-m-d')])
            ;
        });
    }

    /**
     * @param Currency[] $currencies
     * @throws \ErrorException
     */
    public function createMany(array $currencies): void
    {
        try {
            foreach ($currencies as $currency) {
                $this->createOne($currency);
            }
        } catch (IntegrityException $e) {
            if (23000 === $e->getCode()) {
                throw new \ErrorException('Rates for this day always collected');
            }
        }
    }
}
