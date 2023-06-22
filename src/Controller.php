<?php

namespace Gino\Yaf\Kernel;

use Gino\Yaf\Kernel\Exception\UnsupportedException;

class Controller extends \Yaf\Controller_Abstract {

    public function forward($arg1, $args2 = NULL, $args3 = NULL, $args4 = NULL) {
        // forward forbidden
        throw new UnsupportedException();
    }

    /**
     * @return Request
     */
    public function getRequest(): ?object {
        return App::request();
    }

    /**
     * @return Response
     */
    public function getResponse(): ?object {
        return App::response();
    }


}