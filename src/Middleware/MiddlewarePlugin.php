<?php

namespace Gino\Yaf\Kernel\Middleware;

use Gino\Yaf\Kernel\Exception\MiddlewareBreakOff;
use Gino\Yaf\Kernel\Exception\MiddlewareFailure;
use Gino\Yaf\Kernel\App;
use Gino\Yaf\Kernel\Log;
use Gino\Yaf\Kernel\Request;
use Gino\Yaf\Kernel\Router\Route;
use Gino\Phplib\ArrayObject;
use Yaf\Request_Abstract;
use Yaf\Response_Abstract;
use \Yaf\Plugin_Abstract;

class MiddlewarePlugin extends Plugin_Abstract {

    const IS_CALL = '!@#middlewar-called#@!';

    /** @var ArrayObject */
    protected $classes;

    public function __construct(array $config) {
        $this->classes = new ArrayObject($config);
    }

    /**
     * 控制器分发前
     *
     * @param Request_Abstract $request
     * @param Response_Abstract $response
     * @return bool
     * @throws MiddlewareFailure
     */
    public function dispatchLoopStartup(Request_Abstract $request, Response_Abstract $response) {
        $this->doHandler($request, $response);
        return true;
    }

    /**
     * 控制器分发后
     *
     * @param Request_Abstract $request
     * @param Response_Abstract $response
     * @return bool
     * @throws MiddlewareFailure
     */
    public function dispatchLoopShutdown(Request_Abstract $request, Response_Abstract $response) {
        $this->doShutdown($request, $response);
        return true;
    }

    /**
     * @param Request_Abstract $request
     * @param Response_Abstract $response
     * @return bool
     * @throws MiddlewareFailure
     */
    public function doHandler(Request_Abstract $request, Response_Abstract $response) {
        $middlewares = $this->getMiddlewares($request);

        if (empty($middlewares)) {
            return true;
        }

        array_walk($middlewares, function (&$name) {
            $parts = explode(':', $name, 2);
            $class = $this->classes->get($parts[0], false);
            if (false === $class) {
                throw new MiddlewareFailure(sprintf('unknown middleware "%s"', $name));
            }
            $name = [$class, $parts[1] ?? ''];
        });

        $called = [];

        try {
            $this->nextCall(App::request(), $middlewares, function (IMiddleware $middleware, callable $next) use ($request, &$called) {
                $middleware->handler(App::request(), $next);
                // 执行后再执行
                array_push($called, [$middleware, '']);
                $request->setParam(static::IS_CALL, $called);
            });
        } catch (MiddlewareBreakOff $ex) {
            $this->doShutdown($request, $response);
            throw $ex;
        }

        return true;
    }

    /**
     * @param Request_Abstract $request
     * @param Response_Abstract $response
     * @return bool
     * @throws MiddlewareFailure
     */
    public function doShutdown(Request_Abstract $request, Response_Abstract $response) {
        $middlewares = $this->getCalledMiddlewares($request);

        if (empty($middlewares)) {
            return true;
        }

        $this->nextCall(App::request(), $middlewares, function (IMiddleware $middleware, callable $next) {
            $middleware->shutdown(App::request(), $next);
        });
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
     * 获取已执行的中间件对象，倒叙
     *
     * @return array<IMiddleware>
     */
    public function getCalledMiddlewares(Request_Abstract $request): array {
        return $request->getParam(static::IS_CALL, []);
    }

    /**
     * 执行中间件
     *
     * @param Request $request
     * @param array $middlewares
     * @throws MiddlewareFailure
     */
    public function nextCall(Request $request, array &$middlewares, callable $callback) {
        if (empty($middlewares)) return;
        [$class_name, $class_args] = array_shift($middlewares);

        if ($class_name instanceof IMiddleware) {
            $object = $class_name;
        } else {
            $object = new $class_name($class_args);
            if (!($object instanceof IMiddleware)) {
                throw new MiddlewareFailure('unsupported middleware ' . $class_name);
            }
        }

        $callback($object, function () use ($request, $middlewares, $callback) {
            $this->nextCall($request, $middlewares, $callback);
        });

    }

}