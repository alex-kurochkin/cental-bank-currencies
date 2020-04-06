<?php

namespace common\components\exceptions;

use common\components\Response;
use filsh\yii2\oauth2server\filters\ErrorToExceptionFilter;
use Yii;
use yii\web\HttpException;

class OauthException extends ErrorToExceptionFilter
{

    /** @inheritdoc */
    public function afterAction($event)
    {
        try {
            parent::afterAction($event);
        } catch (HttpException $e) {
            // replace module errors with ours

            /** @var $e HttpException */
            /** @var Response $response */
            $response = Yii::$app->response;
            $response->data = $response->error($response::ERROR_BAD_REQUEST, $e->getMessage());
            $response->send();
        }
    }
}
