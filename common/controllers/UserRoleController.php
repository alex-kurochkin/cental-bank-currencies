<?php

namespace common\controllers;

use yii\filters\AccessControl;
use yii\web\UnauthorizedHttpException;

abstract class UserRoleController extends RestController
{

    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [[
                'allow' => true,
                'roles' => ['@'],
            ]],
            'denyCallback' => function ($rule, $action) {
                throw new UnauthorizedHttpException();
            },
        ];
        return $behaviors;
    }
}
