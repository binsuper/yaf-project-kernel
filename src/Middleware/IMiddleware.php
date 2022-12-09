<?php

namespace Gino\Yaf\Kernel\Middleware;

use Gino\Yaf\Kernel\Request;
use Closure;

interface IMiddleware {

    public function handler(Request $request, Closure $next);

    public function shutdown(Request $request, Closure $next);

}