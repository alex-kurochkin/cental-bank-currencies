<?php

namespace common\domain;

use Closure;
use Yii;
use yii\caching\CacheInterface;

class AppCache
{
    const PREFIX = 'app.';
    const TTL = 300; // 5 min

    const GLOBAL_TLD = 'globaltld';

    public function exists(string $key): bool
    {
        return self::getCache()->exists(self::PREFIX . $key);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return self::getCache()->get(self::PREFIX . $key);
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return bool
     */
    public function set(string $key, $value)
    {
        self::getCache()->set(self::PREFIX . $key, $value, self::TTL);
        return $value;
    }

    /**
     * @param string  $key
     * @param Closure $closure
     * @return bool
     */
    public function getOrSet(string $key, Closure $closure)
    {
        return self::getCache()->getOrSet(self::PREFIX . $key, $closure, self::TTL);
    }

    private static function getCache(): CacheInterface
    {
        return Yii::$app->cache;
    }
}
