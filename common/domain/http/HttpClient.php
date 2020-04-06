<?php

namespace common\domain\http;

use common\domain\utils\ErrorMessageBuilder;
use common\domain\utils\Json;
use InvalidArgumentException;
use yii\base\Model;
use yii\httpclient\Client;
use yii\httpclient\Exception;
use yii\httpclient\Response;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;

class HttpClient
{

    /** @var Client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param string      $path
     * @param string|null $returnType
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws ServerErrorHttpException
     * @throws UnauthorizedHttpException
     * @throws \Exception
     * @throws NotFoundHttpException
     */
    public function get(string $path, string $returnType = null)
    {
        $response = $this->client->get($path)->send();
        return $this->validateResponse($response, $returnType);
    }

    /**
     * @param string      $path
     * @param Model|null  $dto
     * @param string|null $returnType
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws ServerErrorHttpException
     * @throws UnauthorizedHttpException
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function getWithQueryDto(string $path, Model $dto = null, string $returnType = null)
    {
        $dto = $this->validateModel($dto);
        return $this->getWithQueryArray($path, $dto->attributes, $returnType);
    }

    /**
     * @param string      $path
     * @param array|null  $query
     * @param string|null $returnType
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws ServerErrorHttpException
     * @throws UnauthorizedHttpException
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function getWithQueryArray(string $path, array $query = null, string $returnType = null)
    {
        $response = $this->client->get($path, $query)->send();
        return $this->validateResponse($response, $returnType);
    }

    /**
     * @param string      $path
     * @param string|null $returnType
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws ServerErrorHttpException
     * @throws UnauthorizedHttpException
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function post(string $path, string $returnType = null)
    {
        $response = $this->client->post($path)->send();
        return $this->validateResponse($response, $returnType);
    }

    /**
     * @param string      $path
     * @param Model|null  $dto
     * @param string|null $returnType
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws ServerErrorHttpException
     * @throws UnauthorizedHttpException
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function postFormDto(string $path, Model $dto = null, string $returnType = null)
    {
        $dto = $this->validateModel($dto);
        return $this->postFormArray($path, $dto->attributes, $returnType);
    }

    /**
     * @param string      $path
     * @param array|null  $form
     * @param string|null $returnType
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws ServerErrorHttpException
     * @throws UnauthorizedHttpException
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function postFormArray(string $path, array $form = null, string $returnType = null)
    {
        $response = $this->client->post($path, $form)->send();
        return $this->validateResponse($response, $returnType);
    }

    /**
     * @param string      $path
     * @param Model|null  $dto
     * @param string|null $returnType
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws ServerErrorHttpException
     * @throws UnauthorizedHttpException
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function putFormDto(string $path, Model $dto = null, string $returnType = null)
    {
        $dto = $this->validateModel($dto);
        return $this->putFormArray($path, $dto->attributes, $returnType);
    }

    /**
     * @param string      $path
     * @param array|null  $form
     * @param string|null $returnType
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws ServerErrorHttpException
     * @throws UnauthorizedHttpException
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function putFormArray(string $path, array $form = null, string $returnType = null)
    {
        $response = $this->client->put($path, $form)->send();
        return $this->validateResponse($response, $returnType);
    }

    /**
     * @param string      $path
     * @param string|null $returnType
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws ServerErrorHttpException
     * @throws UnauthorizedHttpException
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function delete(string $path, string $returnType = null)
    {
        $response = $this->client->delete($path)->send();
        return $this->validateResponse($response, $returnType);
    }

    /**
     * @param string      $path
     * @param Model|null  $dto
     * @param string|null $returnType
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws ServerErrorHttpException
     * @throws UnauthorizedHttpException
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function deleteWithQueryDto(string $path, Model $dto = null, string $returnType = null)
    {
        $dto = $this->validateModel($dto);
        return $this->deleteWithQueryArray($path, $dto->attributes, $returnType);
    }

    /**
     * @param string      $path
     * @param array|null  $query
     * @param string|null $returnType
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws ServerErrorHttpException
     * @throws UnauthorizedHttpException
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function deleteWithQueryArray(string $path, array $query = null, string $returnType = null)
    {
        $response = $this->client->delete($path, $query)->send();
        return $this->validateResponse($response, $returnType);
    }

    /**
     * @param Model $model
     * @return Model
     */
    private function validateModel(Model $model): Model
    {
        if (!$model->validate()) {
            throw new InvalidArgumentException(ErrorMessageBuilder::build($model->errors));
        }

        return $model;
    }

    /**
     * @param Response    $response
     * @param string|null $responseType
     * @return mixed
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     * @throws ServerErrorHttpException
     * @throws UnauthorizedHttpException
     */
    private function validateResponse(Response $response, string $responseType = null)
    {
        switch ($response->statusCode) {
            case 500:
                throw new ServerErrorHttpException($response->content);

            case 404:
                throw new NotFoundHttpException($response->content);

            case 403:
                throw new ForbiddenHttpException($response->content);

            case 401:
                throw new UnauthorizedHttpException($response->content);

            case 201:
            case 200:
                // no response type - return raw
                if (!$responseType) {
                    return $response->data;
                }

                // read response
                $result = Json::parse($response->content, $responseType);
                if (empty($result)) {
                    return $result;
                }

                // validate array
                if (is_array($result)) {
                    if ($result[0] instanceof Model) {
                        foreach ($result as $item) {
                            $this->validateModel($item);
                        }
                    }
                }

                // validate object
                return $result instanceof Model ? $this->validateModel($result) : $result;

            default:
                throw new HttpClientException($response->content);
        }
    }
}
