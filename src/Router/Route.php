<?php

namespace Gino\Yaf\Kernel\Router;

use Gino\Yaf\Kernel\Exception\BadConfigurationException;
use Gino\Yaf\Kernel\App;
use Gino\Yaf\Kernel\Log;
use FastRoute\Dispatcher;
use Yaf\Application;

class Route implements \Yaf\Route_Interface {


    const MIDDLEWARE = '!@#middlewar#@!';

    /** @var Dispatcher */
    public $dispatcher;


    public function __construct() {
        // 初始化路由
        $this->dispatcher = $this->getRouteDispatcher();
    }

    /**
     *  路由
     *
     * @return Dispatcher
     */
    public function getRouteDispatcher() {
        try {
            return new Dispatcher\GroupCountBased(App::config()->get('router'));
        } catch (\Throwable $ex) {
            throw BadConfigurationException::invalid('router', $ex);
        }
    }

    /**
     * @param \Yaf\Request_Abstract $request
     * @return bool|void
     */
    public function route($request) {
        $request->setRouted();
        $httpMethod = $request->getMethod();
        $uri        = $request->getRequestUri();

        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);
        if ($routeInfo[0] != Dispatcher::FOUND) {
            Log::channel()->debug('route dispatch failed', ['method' => $httpMethod, 'uri' => $uri]);
            return true;
        }

        $handler = $routeInfo[1];
        $vars    = $routeInfo[2];
        $config  = Application::app()->getConfig();

        $route      = $handler['route'];
        $middleware = $handler['middleware'] ?? [];

        $route      = explode('@', $route);
        $module     = $route[0] ?? null;
        $controller = $route[1] ?? null;
        $action     = $route[2] ?? null;

        if (is_null($controller)) {
            $controller = $config->get('application.dispatcher.defaultController') ?: 'index';
            $action     = $config->get('application.dispatcher.defaultAction') ?: 'index';
        } else if (is_null($action)) {
            $action = $config->get('application.dispatcher.defaultAction') ?: 'index';
        }

        Log::channel()->debug('route dispatch to', ['Module' => $module, 'Controller' => $controller, 'Action' => $action]);
        $request->setModuleName($module);
        $request->setControllerName($controller);
        $request->setActionName($action);
        // 中间件
        $request->setParam(self::MIDDLEWARE, $middleware);
        // 参数
        App::request()->setQuery($vars);

        return true;
    }

    /**
     * @param array $info
     * @param array|null $query
     * @return bool
     */
    function assemble($info, $query = null) {
        return true;
    }

}