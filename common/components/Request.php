<?php

namespace common\components;

use common\components\exceptions\JsonErrorException;

/**
 * Class Request
 * @package common\components
 *
 * @property null|array $rawBodyJson Parsed raw JSON data
 */
class Request extends \yii\web\Request
{

    /**
     * Get the body or a request as array.
     *
     * @return array|null Parsed data or null if the data supplied is not a valid json
     * @throws JsonErrorException
     */
    public function getRawBodyJson(): array
    {
        $json = json_decode($this->rawBody, true);
        $jsonLastError = json_last_error();

        if ($jsonLastError !== JSON_ERROR_NONE) {
            throw new JsonErrorException($jsonLastError);
        }

        return $json;
    }
}
