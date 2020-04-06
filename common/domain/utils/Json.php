<?php

namespace common\domain\utils;

use JsonMapper;
use JsonMapper_Exception;
use RuntimeException;

class Json
{

    /** @var JsonMapper */
    private static $mapper;

    /**
     * @return JsonMapper
     */
    private static function getMapper()
    {
        if (!self::$mapper) {
            self::$mapper = new JsonMapper();
        }

        return self::$mapper;
    }

    /**
     * @param string $json
     * @param string $type class type
     *
     * @return mixed
     */
    public static function parse($json, $type)
    {
        $object = json_decode($json);
        if (is_array($object)) {
            return self::mapArrayToType($object, $type);
        }

        return self::mapObjectToType($object, $type);
    }

    /**
     * @param mixed  $object
     * @param string $type class type
     *
     * @return mixed
     */
    public static function mapArrayToType(array $object, string $type)
    {
        $items = [];
        foreach ($object as $key => $item) {
            $items[$key] = self::mapObjectToType($item, $type);
        }

        return $items;
    }

    /**
     * @param mixed  $object
     * @param string $type class type
     *
     * @return mixed
     */
    public static function mapObjectToType($object, string $type)
    {
        // read object
        try {
            return self::getMapper()->map($object, new $type());
        } catch (JsonMapper_Exception $e) {
            throw new RuntimeException('JSON error: ' . $e->getMessage(), 0, $e);
        }
    }
}
