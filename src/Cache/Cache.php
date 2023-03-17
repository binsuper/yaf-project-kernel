<?php

namespace Gino\Yaf\Kernel\Cache;

/**
 * @method static bool set(string $key, mixed $value, null|int|\DateInterval $ttl = null)
 * @method static mixed get(string $key, mixed $default = null)
 * @method static bool delete(string $key)
 * @method static bool clear()
 * @method static iterable getMultiple(iterable $keys, mixed $default = null)
 * @method static bool setMultiple(iterable $values, null|int|\DateInterval $ttl = null)
 * @method static bool deleteMultiple(iterable $keys)
 * @method static bool has(string $key)
 * @method static mixed remember(string $key, \DateTimeInterface|\DateInterval|int $ttl, \Closure $callback)
 * @method static mixed rememberForever(string $key, \Closure $callback)
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
        throw new \Exception('undefined method ' . $name . ' in ' . gettype($cache));
    }

}