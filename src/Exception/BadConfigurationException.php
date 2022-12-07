<?php

namespace Gino\Yaf\Kernel\Exception;

class BadConfigurationException extends Exception {

    const TYPE_MISS    = 1;
    const TYPE_INVALID = 2;

    /**
     * configuration is not found
     *
     * @param $key
     * @param null $ex
     * @return static
     */
    public static function miss($key, $ex = null) {
        return new static(sprintf('configuration <%s> is not found', $key), static::TYPE_MISS, $ex);
    }

    /**
     * configuration is invalid
     *
     * @param $key
     * @param null $ex
     * @return static
     */
    public static function invalid($key, $ex = null) {
        return new static(sprintf('configuration <%s> is invalid', $key), static::TYPE_INVALID, $ex);
    }

}