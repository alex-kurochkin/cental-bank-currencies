<?php

namespace common\components;

/** @inheritdoc */
class Url extends \yii\helpers\Url
{

    const PUBLIC_INDEX = '/site/site/index';
    const PUBLIC_HOSTING = '/hosting';
    const PUBLIC_HOSTING_SETUP = '/hosting/setup';

    /**
     * Check whether the current page is a home page
     *
     * @param string|null $url
     *
     * @return bool Current page is a home page or not
     */
    public static function isHomePage(string $url = null): bool
    {
        return ($url ?? Url::current()) === Url::home();
    }
}
