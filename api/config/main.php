<?php

use common\components\Request;
use common\components\Response;
use common\components\ServiceRoute;

$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'api\controllers',
    'bootstrap' => ['log'],
    'modules' => [],
    'components' => [
        'request' => [
            'class' => Request::class,
            'cookieValidationKey' => 'Mj5RhfOP2KbMvyA_XbitIHjJHhJP56D',
            'enableCsrfValidation' => false,
            'enableCsrfCookie' => false,
            'parsers' => [
                'application/json' => yii\web\JsonParser::class,
            ],
            'csrfParam' => '_csrf-api',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-api', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the api
            'name' => 'advanced-api',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [ // OPTIONS required for CORS preflight requests
                'GET,OPTIONS currencies/<from:[\d-]+>/<to[\d-]+>' => 'currencies/list',
            ],
        ],
        'response' => [
            'class' => Response::class,
        ],
        'service' => [
            'class' => ServiceRoute::class,
            'services' => [],
        ],
        'formatter' => [
            'dateFormat' => 'yyyy-MM-dd',
            'datetimeFormat' => 'yyyy-MM-dd HH:mm:ss',
            'timeFormat' => 'HH:mm:ss',
        ],
    ],
    'params' => $params,
];
