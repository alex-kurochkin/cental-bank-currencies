<?php

namespace common\components;

/**
 * Class ActiveQuery
 */
class ActiveQuery extends \yii\db\ActiveQuery
{

    /** @inheritdoc */
    public function count($q = '*', $db = null)
    {
        return (int)parent::count($q, $db);
    }
}
