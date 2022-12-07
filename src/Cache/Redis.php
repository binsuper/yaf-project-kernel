<?php

namespace Gino\Yaf\Kernel\Cache;

use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Traits\RedisClusterProxy;
use Symfony\Component\Cache\Traits\RedisProxy;

/**
 * @mixin \Predis\ClientInterface|\Redis|\RedisCluster|RedisClusterProxy|RedisProxy
 */
class Redis {

    /** @var \Redis|\RedisCluster|RedisClusterProxy|RedisProxy|\Predis\ClientInterface */
    protected $_conn = null;

    public function __construct(array $options = []) {
        $this->init($options);
    }

    public function __call($method, $arguments) {
        if (method_exists($this->conn(), $method)) {
            return call_user_func([$this->conn(), $method], ...$arguments);
        }
        throw new \RuntimeException(sprintf('Call to undefined method %s:%s()', static::class, $method));
    }

    /**
     * @param array $options
     */
    protected function init(array $options) {
        $host     = $options['host'] ?? '';
        $port     = $options['port'] ?? '';
        $pass     = $options['password'] ?? '';
        $database = $options['database'] ?? '';

        $dsn         = sprintf('redis://%s%s%s%s', $pass ? $pass . '@' : '', $host, $port ? ':' . $port : '', $database !== '' ? '/' . $database : '');
        $this->_conn = RedisAdapter::createConnection($dsn, $options);
    }

    /**
     * @return \Predis\ClientInterface|\Redis|\RedisCluster|RedisClusterProxy|RedisProxy|null
     */
    public function conn() {
        return $this->_conn;
    }

}