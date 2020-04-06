<?php

namespace common\components;

use common\components\exceptions\ForbiddenException;
use common\components\exceptions\JsonErrorException;
use common\components\exceptions\ModelValidationException;
use app\domain\common\logging\Log;
use app\domain\common\persistence\exceptions\EntityNotFoundException;
use common\domain\Sentry;
use Exception;
use InvalidArgumentException;
use LogicException;
use Yii;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;
use yii\web\UnsupportedMediaTypeHttpException;

/** Class ErrorHandler */
class ErrorHandler extends \yii\web\ErrorHandler
{

    /** Rollback data base if error */
    protected function rollback(): void
    {
        if (isset(Yii::$app->db)) {
            $trans = Yii::$app->db->transaction;
            if (!is_null($trans) && $trans->isActive) {
                $trans->rollBack();
            }

        }
    }

    /** @inheritdoc */
    public function handleException($exception)
    {
        $this->rollback();
        parent::handleException($exception);
    }

    /** @inheritdoc */
    public function handleFatalError()
    {
        $this->rollback();
        parent::handleFatalError();
    }

    /**
     * Renders the exception. Depending on type of exception sets http status of response or gets special data from ApiException subclasses.
     *
     * @param Exception $exception the exception to be rendered.
     */
    protected function renderException($exception): void
    {
        /** Handles rare db exception. */
        // if ($exception instanceof \yii\db\Exception) {
        // 	// require Yii::getAlias('@siteViewsDir/no_db_connection_error.php');
        // 	\Yii::$app->end();
        // }

        // send to sentry
        Sentry::captureException($exception);

        $response = Yii::$app->response;
        $controller = Yii::$app->controller;
        if ($controller instanceof RestController || $controller instanceof Controller) {
            switch (true) {
                case $exception instanceof ForbiddenHttpException:
                case $exception instanceof ForbiddenException:
                    $response->data = $response->error($response::ERROR_FORBIDDEN, null, $exception->getMessage());
                    break;
                case $exception instanceof UnauthorizedHttpException:
                    $response->data = $response->error($response::ERROR_UNAUTHORIZED);
                    break;
                case $exception instanceof MethodNotAllowedHttpException:
                    $response->data = $response->error($response::ERROR_BAD_METHOD);
                    break;
                case $exception instanceof EntityNotFoundException:
                case $exception instanceof NotFoundHttpException:
                    $response->data = $response->error($response::ERROR_NOT_FOUND, 'There is no resource at the endpoint ' . Yii::$app->request->url, $exception->getMessage());
                    break;
                case $exception instanceof InvalidArgumentException:
                case $exception instanceof LogicException:
                case $exception instanceof JsonErrorException:
                case $exception instanceof BadRequestHttpException:
                    $response->data = $response->error($response::ERROR_BAD_REQUEST, $exception->getMessage());
                    break;
                case $exception instanceof UnsupportedMediaTypeHttpException:
                    $response->data = $response->error($response::ERROR_CONTENT_TYPE, 'The requested content type is not supported for this endpoint');
                    break;
                case $exception instanceof ModelValidationException:
                    $response->data = $response->error($response::ERROR_VALIDATION, $exception->getModel()->errors, 'Some of the supplied data did not pass validation.');
                    break;
                default:
                    Log::error((string)$exception);
                    $response->data = $response->error($response::ERROR_INTERNAL, IS_DEV ? $exception->getMessage() : null);
                    break;
            }
            if (empty($response->headers->get('Access-Control-Allow-Origin'))) {
                $response->headers->add('Access-Control-Allow-Origin', '*'); //fixme introduce a list of allowed hosts
            }
            $response->send();
        } else {
            parent::renderException($exception);
        }
    }
}
