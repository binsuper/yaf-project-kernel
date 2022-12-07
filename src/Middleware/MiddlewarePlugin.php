<?php

namespace Gino\Yaf\Kernel\Middleware;

use Gino\Yaf\Kernel\Exception\MiddlewareFailure;
use Gino\Yaf\Kernel\App;
use Gino\Yaf\Kernel\Router\Route;
use Gino\Phplib\ArrayObject;
use Yaf\Request_Abstract;
use Yaf\Response_Abstract;
use \Yaf\Plugin_Abstract;

class MiddlewarePlugin extends Plugin_Abstract {

    /** @var ArrayObject */
    protected $classes;

    public function __construct(array $config) {
        $this->classes = new ArrayObject($config);
    }

    /**
     * 如果在一个请求处理过程中, 发生了forward, 则这个事件会被触发多次
     *
     * @param Request_Abstract $request
     * @param Response_Abstract $response
     * @return bool
     */
    public function preDispatch(Request_Abstract $request, Response_Abstract $response) {
        $middlewares = $this->getMiddlewares($request);

        if (empty($middlewares)) {
            // nothing
            return true;
        }

        array_walk($middlewares, function (&$name) {
            $class = $this->classes->get($name, false);
            if (false === $class) {
                throw new MiddlewareFailure(sprintf('unknown middleware "%s"', $name));
            }
            $name = $class;
        });

        $this->nextCall($request, $middlewares);

        return true;
    }

    /**
     * 获取中间件
     *
     * @return array
     */
    public function getMiddlewares(Request_Abstract $request): array {
        return $request->getParam(Route::MIDDLEWARE, []);
    }

    /**
     * 执行中间件
     *
     * @param Request_Abstract $request
     * @param array $middlewares
     * @throws MiddlewareFailure
     */
    public function nextCall(Request_Abstract $request, array &$middlewares) {
        if (empty($middlewares)) return;

        $class_name = array_shift($middlewares);

        $object = new $class_name();
        if (!($object instanceof IMiddleware)) {
            throw new MiddlewareFailure('unsupported middleware ' . $class_name);
        }

        $object->handler($request, function () use ($request, $middlewares) {
            $this->nextCall($request, $middlewares);
        });
    }

}