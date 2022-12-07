<?php

namespace Gino\Yaf\Kernel\Cache;

use Gino\Phplib\ArrayObject;
use Gino\Yaf\Kernel\Cache\Marshaller\JsonMarshaller;
use Gino\Yaf\Kernel\Exception\BadConfigurationException;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Marshaller\MarshallerInterface;

class Manager {

    /** @var array */
    private static $global_options = [];

    /** @var array */
    private static $global_caches = [];

    protected $options = [];

    private function __construct(array $options) {
        $this->options = $options;
    }

    /**
     * 初始化
     *
     * @param array $options
     */
    public static function startup(array $options) {
        static::$global_options = $options;
    }

    /**
     * find the config of name
     *
     * @param string $name
     * @return array|null
     */
    public static function findOptions(string $name = ''): ?array {
        $array   = new ArrayObject(static::$global_options);
        $default = $array->get('default', false);
        $stores  = $array->get('stores', []);

        if (!$name) {
            $name = $default;
        }

        if (!$name || !isset($stores[$name])) {
            return null;
        }

        return $stores[$name];
    }

    /**
     * @param string $name
     * @return Cache|null
     * @throws BadConfigurationException
     */
    public static function cache(string $name = ''): ?Cache {

        if (isset(static::$global_caches[$name])) {
            return static::$global_caches[$name];
        }

        $config = static::findOptions($name);

        if (!$config) {
            return null;
        }

        $manager = new static($config);
        $pool    = $manager->createPool();
        if (!$pool) {
            return null;
        }

        static::$global_caches[$name] = new Cache($pool);
        return static::$global_caches[$name];
    }

    /**
     * @param array $options
     * @return CacheItemPoolInterface
     * @throws BadConfigurationException
     */
    protected function createPool(): CacheItemPoolInterface {
        $driver = $this->options['driver'] ?? false;
        if (!$driver) {
            throw new BadConfigurationException('cache driver option must be set', BadConfigurationException::TYPE_MISS);
        }

        return call_user_func([$this, sprintf('%sPool', $driver)], $this->options);
    }

    /**
     * @return MarshallerInterface|null
     */
    protected function getMarshaller(): ?MarshallerInterface {
        if (!isset($this->options['marshaller'])) {
            return null;
        }

        $class = $this->options['marshaller'];

        if (!is_a($class, MarshallerInterface::class, true)) {
            return null;
        }

        return new $class();
    }

    /**
     * @param array $options
     * @return CacheItemPoolInterface
     */
    protected function redisPool(): CacheItemPoolInterface {
        $redis = new Redis($this->options);
        return new RedisAdapter($redis->conn(), '', 0, $this->getMarshaller());
    }


}