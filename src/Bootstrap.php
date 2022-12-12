<?php


namespace Gino\Yaf\Kernel;

use Gino\Yaf\Kernel\Response\ResponsePlugin;
use Yaf\Bootstrap_Abstract;
use Yaf\Dispatcher;
use Gino\Yaf\Kernel\Exception\ErrorHandler;
use Gino\Yaf\Kernel\Middleware\MiddlewarePlugin;
use Gino\Yaf\Kernel\Router\Route;

/**
 * @name Bootstrap
 * @desc 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * 这些方法, 都接受一个参数:Yaf\Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends Bootstrap_Abstract {

    protected $error_handler_class = ErrorHandler::class;

    public function _initException(Dispatcher $dispatcher) {
        // 处理错误
        set_error_handler(function () {
            return forward_static_call([$this->error_handler_class, 'error'], ...func_get_args());
        });

        // 处理异常
        set_exception_handler(function () {
            forward_static_call([$this->error_handler_class, 'exception'], ...func_get_args());

            // 异常阻断后刷新内容
            App::response()->flush();
        });
    }

    public function _initRouter(Dispatcher $dispatcher) {
        $dispatcher->registerPlugin(new MiddlewarePlugin(App::config()->get('kernel.middlewares', [])));
        $dispatcher->registerPlugin(new ResponsePlugin());
        $dispatcher->getRouter()->addRoute('FastRoute', new Route());
    }

}
