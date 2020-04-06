<?php

namespace common\components;

use Yii;

/**
 * Class Module
 */
class Module extends \yii\base\Module
{

    /**
     * Translate text
     *
     * @param string $message
     * @param array $params
     * @param string $category
     * @param string $language
     *
     * @return string
     */
    public static function t(string $message, array $params = [], string $category = 'app', string $language = null)
    {
        return Yii::t($category, $message, $params, $language);
    }
}
