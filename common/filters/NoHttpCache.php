<?php

namespace common\filters;

use Yii;
use yii\base\ActionFilter;
use yii\web\Response;

class NoHttpCache extends ActionFilter
{
    public $enabled = true;

    public function beforeAction($action)
    {
        if (!$this->enabled) {
            return true;
        }

        $verb = Yii::$app->getRequest()->getMethod();
        if ($verb !== 'GET' && $verb !== 'HEAD') {
            return true;
        }

        $this->sendCacheControlHeader(Yii::$app->getResponse());
        return true;
    }

    protected function sendCacheControlHeader(Response $response)
    {
        if (!headers_sent() && Yii::$app->getSession()->getIsActive()) {
            header_remove('Expires');
            header_remove('Cache-Control');
            header_remove('Last-Modified');
            header_remove('Pragma');
        }

        $response->getHeaders()->set('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
        $response->getHeaders()->set('Last-Modified', gmdate("D, d M Y H:i:s") . ' GMT');
        $response->getHeaders()->set('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $response->getHeaders()->set('Pragma', 'no-cache');
    }
}
