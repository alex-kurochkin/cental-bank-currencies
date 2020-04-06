<?php
declare(strict_types=1);

namespace api\models\currency\repositories\ars;

use common\domain\persistence\BaseAr;

/**
 * Class CurrencyAr
 * @package api\models\currency\repositories\ars
 *
 * @property int $id
 * @property string $valueID
 * @property int $numCode
 * @property string $charCode
 * @property string $name
 * @property int $nominal
 * @property float $value
 * @property string $date (DateTime)
 */
class CurrencyAr extends BaseAr
{

    const ID = 'id';

    const DATE = 'date';

    const MAPPING = [
        self::ID => ['id', 'int'],
        'valuteID' => 'valuteId',
        'numCode' => ['numCode', 'int'],
        'charCode' => 'charCode',
        'name' => 'name',
        'nominal' => ['nominal', 'int'],
        'value' => 'value',
        self::DATE => ['date', 'date', false],
    ];

    /** @inheritdoc */
    public static function tableName(): string
    {
        return 'currency';
    }
}
