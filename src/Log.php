<?php

namespace Gino\Yaf\Kernel;

use Gino\Phplib\Log\Logger;

class Log {

    /** @var Logger */
    private static $logger = null;

    /**
     * 返回日志通道
     *
     * @param string $channel
     * @return \Monolog\Logger
     * @throws \Gino\Phplib\Error\BadConfigurationException
     */
    public static function channel(string $channel = ''): \Monolog\Logger {
        if (is_null(static::$logger)) {
            static::$logger = new Logger(App::config()->get('logger', []));
        }
        return static::$logger->channel($channel);
    }


}