<?php

namespace Gino\Yaf\Kernel\Cache;

/**
 *
 * @method static mixed remember(string $key, \DateTimeInterface|\DateInterval|int $ttl, \Closure $callback)
 *
 * @see CacheHandler
 */
class Cache {

    /**
     * @inheritDoc
     */
    public static function __callStatic($name, $arguments) {
        $cache = Manager::cache();
        if (method_exists($cache, $name)) {
            return $cache->$name(...$arguments);
        }
        throw new \Exception('undefined method ' . $name);
    }


}