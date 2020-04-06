<?php

namespace common\components\validators;

use common\components\Tools;

/** @inheritdoc */
class DateValidator extends \yii\validators\DateValidator
{

    /** @var bool Whether it is necessary to correct Year 2038 problem automatically? */
    public $fixProblem2038 = true;

    /** @var string Current attribute */
    public $attribute;

    /** @inheritdoc */
    protected function parseDateValue($value)
    {
        // allow unix timestamp
        if (is_numeric($value) && (int)$value > 0) {
            $date = (int)$value;
        } else {
            $date = parent::parseDateValue($value);
            if ($date) {
                $this->timestampAttribute = $this->attribute;
            }
        }

        return $this->fixProblem2038 ? $this->fixDate2038($date) : $date;
    }

    /** @inheritdoc */
    public function validateAttribute($model, $attribute)
    {
        $this->attribute = $attribute;
        parent::validateAttribute($model, $attribute);
    }

    /**
     * Autofix Year 2038 problem
     *
     * @link https://en.wikipedia.org/wiki/Year_2038_problem
     *
     * @param false|int $date
     *
     * @return false|int
     */
    protected function fixDate2038($date)
    {
        if ($date && ($this->type === self::TYPE_DATETIME || $this->type === self::TYPE_DATE)) {
            $date = Tools::dateEndUnixTimeFix($date);
        }

        return $date;
    }
}