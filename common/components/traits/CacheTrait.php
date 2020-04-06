<?php

namespace common\components\traits;

use Yii;
use yii\caching\TagDependency;

/**
 * Trait CacheTrait
 */
trait CacheTrait
{

    /**
     * Get TagDependency for cache
     *
     * @param string $key
     *
     * @return TagDependency
     */
    protected static function getCacheTagDependency(string $key): TagDependency
    {
        return new TagDependency(['tags' => $key]);
    }

    /**
     * Invalidate cache
     *
     * @param string $key
     */
    protected static function cacheTagInvalidate(string $key): void
    {
        TagDependency::invalidate(Yii::$app->cache, $key);
    }
}
