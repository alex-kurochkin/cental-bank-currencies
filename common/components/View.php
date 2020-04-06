<?php

namespace common\components;

/**
 * Class View
 * @package common\components
 * @property string $description
 * @property bool $noindex
 */
class View extends \yii\web\View
{

    public $H1 = '';
    public $header = '';
    public $breadcrumbs = [];

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        if ($description) {
            parent::registerMetaTag(['name' => 'description', 'content' => $description], 'description');
        }
    }

    /**
     * @param string $noindex
     */
    public function setNoindex(string $noindex): void
    {
        if ($noindex) {
            parent::registerMetaTag(['name' => 'robots', 'content' => 'noindex, nofollow'], 'noindex');
        }
    }
}
