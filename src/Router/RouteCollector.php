<?php

namespace Gino\Yaf\Kernel\Router;

class RouteCollector extends \FastRoute\RouteCollector {

    const SCOPE_NULL  = 0;
    const SCOPE_ONE   = 1;
    const SCOPE_GROUP = 2;

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
     * @inheritDoc
     */
    public function addRoute($httpMethod, $route, $handler) {
        if ($route[0] !== '/') {
            $route = '/' . $route;
        }

        $current_middlewares       = $this->current_middlewares ?: $this->current_group_middlewares;
        $this->current_middlewares = false;

        $data['route'] = $handler;
        if ($current_middlewares != false) {
            $data['middleware'] = $current_middlewares;
        }

        parent::addRoute($httpMethod, $route, $data);

    }

    /**
     * @param string $prefix
     * @param callable $callback
     */
    public function addGroup($prefix, callable $callback) {
        $current_group_middlewares       = $this->current_group_middlewares;
        $this->current_group_middlewares = $this->current_middlewares ?: $current_group_middlewares;
        $this->current_middlewares       = false;

        parent::addGroup($prefix, function () use ($callback) {
            call_user_func_array($callback, func_get_args());
        });

        $this->current_group_middlewares = $current_group_middlewares;
    }


}