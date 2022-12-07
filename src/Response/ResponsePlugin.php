<?php

namespace Gino\Yaf\Kernel\Response;

use Gino\Yaf\Kernel\App;
use Yaf\Request_Abstract;
use Yaf\Response_Abstract;
use \Yaf\Plugin_Abstract;

class ResponsePlugin extends Plugin_Abstract {

    /**
     * @inheritDoc
     */
    public function dispatchLoopShutdown(Request_Abstract $request, Response_Abstract $response) {
        App::response()->flush();
    }


}