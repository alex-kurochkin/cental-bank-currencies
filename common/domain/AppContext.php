<?php
declare(strict_types=1);

namespace common\domain;

use \yii\web\User;
use Yii;

class AppContext
{

    public function getUser(): ?User
    {
        return Yii::$app->getUser();
    }

    public function getUserId(): ?int
    {
        $user = $this->getUser();

        return $user ? $user->getId() : null;
    }

    public function getUserIp(): string
    {
        return (string)\Yii::$app->getRequest()->getUserIP();
    }
}
