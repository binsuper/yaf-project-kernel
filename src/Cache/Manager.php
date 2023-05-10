<?php

namespace Gino\Yaf\Kernel\Cache;

use Gino\Phplib\ArrayObject;
use Gino\Yaf\Kernel\Exception\BadConfigurationException;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Marshaller\MarshallerInterface;

/**
 * 配置参考
 * [
 *      // Default Cache Stores Name
 *      'default' => env('cache.default', 'file'),
 *
 *      'namespace' => '',
 *
 *      // Cache Stores Options
 *      'stores'  => [
 *
 *          'file' => [
 *              'driver'    => 'file',
 *              'directory' => 'var/cache/',
 *              'namespace' => '',
 *          ],
 *
 *          'redis' => [
 *              'driver'   => 'redis',
 *              'host'     => '127.0.0.1',
 *              'port'     => '6379',
 *              'password' => '',
 *              'database' => 0,
 *          ],
 *      ],
 * ]
 *
 *
 */
class Manager {

    /** @var array */
    private static $global_options = [];

    /** @var array */
    private static $global_caches = [];

    /** @var string|bool 全局键名前缀 */
    private static $global_namespace = '';

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
        static::setGlobalNamespace($options['namespace'] ?? false);
    }

    /**
     * 获取命名空间
     *
     * @return string|bool
     */
    public static function getGlobalNamespace() {
        return static::$global_namespace;
    }

    /**
     * 设置命名空间
     *
     * @param string|bool $namespace
     */
    public static function setGlobalNamespace($namespace) {
        static::$global_namespace = $namespace;
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
     * @return CacheHandler|null
     * @throws BadConfigurationException
     */
    public static function cache(string $name = ''): ?CacheHandler {

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

        $cache = new CacheHandler($pool);
        if (isset($config['namespace'])) {
            $cache->setNamespace($config['namespace'] ?? '');
        }

        static::$global_caches[$name] = $cache;
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
    protected function filePool(): CacheItemPoolInterface {
        $directory = $this->options['directory'] ?? '';
        return new FilesystemAdapter('', 0, $directory, $this->getMarshaller());
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