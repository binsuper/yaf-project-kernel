<?php

namespace Gino\Yaf\Kernel\Router;

use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteParser\Std;

/**
 * @method static RouteCollector group($prefix, ?callable $callback = null)
 * @method static RouteCollector middleware($middlewares = false)
 * @method static RouteCollector get($route, $handler)
 * @method static RouteCollector post($route, $handler)
 * @method static RouteCollector put($route, $handler)
 * @method static RouteCollector delete($route, $handler)
 * @method static RouteCollector patch($route, $handler)
 * @method static RouteCollector head($route, $handler)
 */
class RouteManager {

    private static $instance = null;

    /** @var RouteCollector */
    private $route_collector = null;

    private function __construct() {
        $this->route_collector = new RouteCollector(new Std(), new GroupCountBased());
    }

    /**
     * 魔术方法 - 静态函数
     */
    public static function __callStatic($method, $arguments) {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        if (method_exists(static::$instance->route_collector, $method)) {
            return static::$instance->route_collector->{$method}(...$arguments);
        }
        throw new \RuntimeException(sprintf('Call to undefined method %s:%s()', get_class(static::$instance->route_collector), $method));
    }

    /**
     * @return array
     */
    public static function result(): array {
        return static::$instance->route_collector->getData();
    }

}