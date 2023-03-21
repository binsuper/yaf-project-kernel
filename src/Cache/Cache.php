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
 * @method static CacheHandler disNamespace(bool $state = true)
 *
 * @method static null|CacheHandler cache(string $name = ''):
 *
 * @see CacheHandler
 */
class Cache {

    /**
     * @inheritDoc
     */
    public static function __callStatic($name, $arguments) {
        if ($name === 'cache') {
            return Manager::$name(...$arguments);
        }
        $cache = Manager::cache();
        return $cache->$name(...$arguments);
    }

    /**
     * 禁用命名空间
     */
    public static function disGlobalNamespace() {
        Manager::setGlobalNamespace(false);
    }

}