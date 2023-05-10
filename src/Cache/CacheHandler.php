<?php

namespace Gino\Yaf\Kernel\Cache;

use Closure;
use DateInterval;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @mixin Psr16Cache
 */
class CacheHandler {

    /** @var string 命名空间 */
    private $namespace = '';

    /** @var bool 禁用命名空间 */
    private $disable_namespace = false;

    /** @var Psr16Cache */
    protected $conn;

    public function __construct(CacheItemPoolInterface $pool) {
        $this->conn = new Psr16Cache($pool);
    }

    public function __call($name, $arguments) {
        return $this->conn->$name(...$arguments);
    }

    /**
     * 设置键名前缀
     *
     * @param string $prefix
     * @return $this
     */
    public function setNamespace(string $prefix): CacheHandler {
        $this->namespace = $prefix;
        return $this;
    }

    /**
     * 获取键名前缀
     *
     * @return string
     */
    public function getNamespace(): string {
        return $this->namespace;
    }

    /**
     * 命名空间状态
     *
     * @param bool $state
     * @return static
     */
    public function disNamespace(bool $state = true) {
        $this->disable_namespace = $state;
        return $this;
    }

    /**
     * @param string $name
     * @return string
     */
    public function getCacheKey(string $name): string {
        $name = strtr($name, ItemInterface::RESERVED_CHARACTERS, str_repeat('_', strlen(ItemInterface::RESERVED_CHARACTERS)));
        $gns = Manager::getGlobalNamespace();
        if (false === $gns || $this->disable_namespace) return $name;
        $ns = $this->getNamespace();
        return sprintf('%s|%s|%s', $gns, $ns, $name);
    }

    /**
     * @param string $key
     * @param null|int|DateInterval $ttl
     * @param Closure $callback
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function remember(string $key, $ttl, Closure $callback) {
        $value = $this->get($key);

        if (!is_null($value)) {
            return $value;
        }

        $this->set($key, $value = $callback(), $ttl);

        return $value;
    }

    /**
     * @param string $key
     * @param Closure $callback
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function rememberForever(string $key, Closure $callback) {
        return $this->remember($key, null, $callback);
    }

    /**
     *
     * Fetches a value from the cache.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed|null
     * @throws InvalidArgumentException
     */
    public function get($key, $default = null) {
        return $this->conn->get($this->getCacheKey($key), $default);
    }

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param string $key The key of the item to store.
     * @param mixed $value The value of the item to store, must be serializable.
     * @param null|int|\DateInterval $ttl Optional. The TTL value of this item. If no value is sent and
     *                                      the driver supports TTL then the library may set a default value
     *                                      for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function set($key, $value, $ttl = null) {
        return $this->conn->set($this->getCacheKey($key), $value, $ttl);
    }

    /**
     * Delete an item from the cache by its unique key.
     *
     * @param string $key The unique cache key of the item to delete.
     *
     * @return bool True if the item was successfully removed. False if there was an error.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function delete($key) {
        return $this->conn->delete($this->getCacheKey($key));
    }

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable $keys A list of keys that can obtained in a single operation.
     * @param mixed $default Default value to return for keys that do not exist.
     *
     * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function getMultiple($keys, $default = null) {
        $keys = array_map(function ($k) {
            return $this->getCacheKey($k);
        }, (array)$keys);
        return $this->conn->getMultiple($keys, $default);
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable $values A list of key => value pairs for a multiple-set operation.
     * @param null|int|\DateInterval $ttl Optional. The TTL value of this item. If no value is sent and
     *                                       the driver supports TTL then the library may set a default value
     *                                       for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $values is neither an array nor a Traversable,
     *   or if any of the $values are not a legal value.
     */
    public function setMultiple($values, $ttl = null) {
        $data = [];
        foreach ($values as $key => $val) {
            $data[$this->getCacheKey($key)] = $val;
        }
        return $this->conn->setMultiple($data, $ttl);
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable $keys A list of string-based keys to be deleted.
     *
     * @return bool True if the items were successfully removed. False if there was an error.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function deleteMultiple($keys) {
        $keys = array_map(function ($k) {
            return $this->getCacheKey($k);
        }, (array)$keys);
        return $this->conn->deleteMultiple($keys);
    }

    /**
     * Determines whether an item is present in the cache.
     *
     * NOTE: It is recommended that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it making the state of your app out of date.
     *
     * @param string $key The cache item key.
     *
     * @return bool
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function has($key) {
        return $this->conn->has($this->getCacheKey($key));
    }

}