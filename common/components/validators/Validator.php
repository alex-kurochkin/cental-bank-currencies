<?php

namespace common\components\validators;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class Validator for adding of custom validation rules
 */
class Validator extends \yii\validators\Validator
{
    /** @var bool Whether validators are set */
    private static $_issetValidators = false;

    /** Set our validators */
    public static function setValidators()
    {
        if (self::$_issetValidators) {
            return;
        }

        $ourValidators = [
            /*'date'     => [
                'class' => 'common\components\validators\DateValidator',
                'type'  => 'date',
            ],
            'datetime' => [
                'class' => 'common\components\validators\DateValidator',
                'type'  => 'datetime',
            ],
            'time'     => [
                'class' => 'common\components\validators\DateValidator',
                'type'  => 'time',
            ],*/
            'model' => 'common\components\validators\ModelValidator',
            'uuid' => 'common\components\validators\UUIDValidator',
            'url' => 'common\components\validators\UrlValidator',
            'domain' => [
                'class' => 'kdn\yii2\validators\DomainValidator',
                'allowURL' => false,
            ],
            'phone' => [
                'class' => 'common\components\validators\PhoneValidator',
                'format' => 0,
            ],
            'exist' => [
                'class' => 'yii\validators\ExistValidator',
                'message' => Yii::t('app', 'Record with value "{value}" not found'),
            ],
        ];

        self::$builtInValidators = ArrayHelper::merge(self::$builtInValidators, $ourValidators);
        self::$_issetValidators = true;
    }
}
