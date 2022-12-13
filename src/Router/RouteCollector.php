<?php

namespace Gino\Yaf\Kernel\Router;

use FastRoute\DataGenerator;
use FastRoute\RouteParser;

class RouteCollector extends \FastRoute\RouteCollector {

    /** @var array|false */
    protected $current_middlewares = false;

    /** @var array|false */
    protected $current_group_middlewares = false;

    /**
     * 中间件
     *
     * @param false $middlewares
     * @return $this
     */
    public function middleware($middlewares = false) {
        if (is_string($middlewares)) {
            $middlewares = explode(',', $middlewares);
        }
        $this->current_middlewares = $middlewares;
        return $this;
    }

    /**
     * @param string|string[] $httpMethod
     * @param string $route
     * @param mixed $handler
     * @return $this
     */
    public function addRoute($httpMethod, $route, $handler) {
//        if ($route[0] !== '/') {
//            $route = '/' . $route;
//        }

        $current_middlewares       = array_merge($this->current_group_middlewares ?: [], $this->current_middlewares ?: []);
        $this->current_middlewares = false;

        $data['route'] = $handler;
        if ($current_middlewares != false) {
            $data['middleware'] = $current_middlewares;
        }

        parent::addRoute($httpMethod, $route, $data);

        return $this;
    }

    /**
     * @param string|callable $prefix
     * @param callable $callback
     * @return $this
     */
    public function addGroup($prefix, ?callable $callback = null) {

        // support 1 arguments
        if (is_callable($prefix)) {
            $callback = $prefix;
            $prefix   = '';
        }

        $current_group_middlewares       = $this->current_group_middlewares;
        $this->current_group_middlewares = array_merge($current_group_middlewares ?: [], $this->current_middlewares ?: []);
        $this->current_middlewares       = false;

        parent::addGroup($prefix, function () use ($callback) {
            call_user_func_array($callback, func_get_args());
        });

        $this->current_group_middlewares = $current_group_middlewares;

        return $this;
    }


    /**
     * @param $prefix
     * @param callable $callback
     * @return $this
     */
    public function group($prefix, ?callable $callback = null) {
        return $this->addGroup($prefix, $callback);
    }


}