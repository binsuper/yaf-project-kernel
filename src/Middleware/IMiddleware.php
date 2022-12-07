<?php

namespace Gino\Yaf\Kernel\Middleware;

use Yaf\Request_Abstract;
use Closure;

interface IMiddleware {

    public function handler(Request_Abstract $request, Closure $next);

}