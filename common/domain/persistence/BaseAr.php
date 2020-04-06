<?php

namespace common\domain\persistence;

use yii\db\ActiveRecord;

class BaseAr extends ActiveRecord
{

    /**
     * Update and delete works only with old primary key
     * @param bool $asArray
     * @return mixed
     */
    public function getOldPrimaryKey($asArray = false)
    {
        return parent::getPrimaryKey($asArray);
    }

    public function getIsNewRecord()
    {
        //@todo check it
        return empty(parent::getPrimaryKey());
    }

}
