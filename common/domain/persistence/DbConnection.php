<?php

namespace common\domain\persistence;

use yii\db\Transaction;

class DbConnection
{

    public function beginTransaction(): Transaction
    {
        return \Yii::$app->db->beginTransaction();
    }
}
