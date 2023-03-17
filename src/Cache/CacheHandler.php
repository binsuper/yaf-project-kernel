<?php

namespace Gino\Yaf\Kernel\Cache;

use Closure;
use DateInterval;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\Cache\Psr16Cache;

class CacheHandler extends Psr16Cache {

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

}