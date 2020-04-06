<?php

namespace common\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\UnauthorizedHttpException;

abstract class AdminRoleController extends RestController
{

    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['@'],
                    'matchCallback' => function ($rule, $action) {
                        return Yii::$app->user->isAdmin;
                    }
                ],
            ],
            'denyCallback' => function ($rule, $action) {
                if (Yii::$app->user) {
                    throw new ForbiddenHttpException();
                }

                throw new UnauthorizedHttpException();
            },
        ];
        return $behaviors;
    }
}
