<?php

namespace common\components;

use common\components\behaviors\TransactionBehavior;
use common\components\exceptions\OauthException;
use Yii;

/**  @inheritdoc */
abstract class Controller extends \yii\web\Controller
{

    /** @inheritdoc */
    public $enableCsrfValidation = false;
    /** @var string[] Actions for which a access token is not required */
    protected $excludeOauthMethods = [];
    /** @var string[] Actions that should use transactions */
    protected $transactionsActions = ['create', 'update', 'delete'];
    /** @var Response Response object */
    protected $response;

    /** @var Request Request object */
    protected $request;

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /** @var ServiceRoute Service route */
    protected $service;

    /** @inheritdoc */
    public function init(): void
    {
        parent::init();
        $this->request = Yii::$app->request;
        $this->response = Yii::$app->response;
        $this->service = Yii::$app->service;
    }

    /** @inheritdoc */
    public function actions(): array
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /** @inheritdoc */
    public function behaviors(): array
    {
        $config = [
            'corsFilter' => [
                'class' => 'yii\filters\Cors',
                'cors' => [
                    // restrict access to
                    'Origin' => [$_SERVER['HTTP_HOST']], // TODO: improve
                    'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
                    'Access-Control-Request-Headers' => ['Origin', 'X-Requested-With', 'Content-Type', 'Accept', 'Authorization'],
                    'Access-Control-Allow-Credentials' => true,
                    // Allow OPTIONS caching
                    'Access-Control-Max-Age' => 86400,
                    //// Allow the X-Pagination-Current-Page header to be exposed to the browser.
                    //'Access-Control-Expose-Headers'    => ['X-Pagination-Current-Page'],
                ],
            ],
            'authenticator' => [
                'class' => 'filsh\yii2\oauth2server\filters\auth\CompositeAuth',
                'authMethods' => [
                    ['class' => 'yii\filters\auth\HttpBearerAuth'],
                    ['class' => 'yii\filters\auth\QueryParamAuth', 'tokenParam' => 'access_token'],
                ],
            ],
            'exceptionFilter' => [
                'class' => OauthException::class,
            ],
            'transaction' => [
                'class' => TransactionBehavior::class,
                'actions' => $this->transactionsActions,
            ],
        ];

        if ($this->isExcludeOauthMethod()) {
            unset($config['authenticator'], $config['exceptionFilter']);
        }

        return $config;
    }

    /**
     * @return bool
     */
    protected function isExcludeOauthMethod(): bool
    {
        return !$this->response->isRest() || in_array($this->action->id, $this->excludeOauthMethods) || $this->action->id === 'options' || in_array('*', $this->excludeOauthMethods);
    }

    /**
     * @param null $id
     */
    public function actionOptions($id = null): void
    {
        if (!$this->request->isOptions) {
            $this->response->setStatusCode(405);
        }
        $options = $id === null ? ['GET', 'POST', 'HEAD', 'OPTIONS'] : ['GET', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'];
        $this->response->getHeaders()
            ->set('Allow', implode(', ', $options));
    }
}
