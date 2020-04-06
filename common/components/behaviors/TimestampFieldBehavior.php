<?php

namespace common\components\behaviors;

use common\components\Tools;
use yii\base\Behavior;
use yii\db\ActiveRecord;

/**
 * TimestampFieldBehavior automatically converts the specified attributes from unix time to a mysql format timestamp.
 *
 * To use TimestampFieldBehavior, insert the following code to your ActiveRecord class:
 *
 * ```php
 * public function behaviors()
 * {
 *     return [[
 *         'class'   => TimestampFieldBehavior::class,
 *         'fields'  => ['execute_time']
 *     ]];
 * }
 * ```
 */
class TimestampFieldBehavior extends Behavior
{

    /** @var string[] List of timestamp field names */
    public $fields = [];

    /** @inheritdoc */
    public function events(): array
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'unix2Timestamp',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'unix2Timestamp',
            ActiveRecord::EVENT_AFTER_FIND => 'timestamp2Unix',
        ];
    }

    /**
     * Convert string timestamp to unix time
     */
    public function unix2Timestamp(): void
    {
        foreach ($this->fields as $field) {
            $model = $this->owner;
            $value = $model->$field;
            if (empty($value)) {
                continue;
            }
            if (is_numeric($value) && (int)$value == $value) {
                $value = Tools::unix2Timestamp($value);
            }
            $model->$field = Tools::dateEndUnixTimeFix($value);
        }
    }

    /**
     * Convert integer unix time to timestamp string
     */
    public function timestamp2Unix(): void
    {
        foreach ($this->fields as $field) {
            $model = $this->owner;
            $value = $model->$field;
            if (empty($value)) {
                continue;
            }
            if (is_string($value)) {
                $model->$field = strtotime($value);
            }
        }
    }
}
