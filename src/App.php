<?php

namespace Gino\Yaf\Kernel;

use Gino\Phplib\Config\Config;

class App {


    /**
     * @param string $path
     * @return string
     */
    public static function path(string $path = ''): string {
        $base = defined('APPLICATION_PATH') ? constant('APPLICATION_PATH') : '';
        if ('' === $path) {
            return $base;
        }
        return $base . DIRECTORY_SEPARATOR . $path;
    }

    /**
     * @return Config
     * @throws \Exception
     */
    public static function config(): Config {
        static $obj = null;
        if ($obj === null) {
            $obj = new Config([
                Config::OPT_ROOT_DIR => static::path('config')
            ]);
        }
        return $obj;
    }

    /**
     * @return Request
     */
    public static function request(): Request {
        static $obj = null;
        if ($obj === null) {
            $obj = new Request();
        }
        return $obj;
    }

    /**
     * @return Response
     */
    public static function response(): Response {
        static $obj = null;
        if ($obj === null) {
            $obj = new Response();
        }
        return $obj;
    }

}