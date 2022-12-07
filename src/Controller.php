<?php

namespace Gino\Yaf\Kernel;

class Controller extends \Yaf\Controller_Abstract {

    public function forward($arg1, $args2 = NULL, $args3 = NULL, $args4 = NULL) {
        // forward forbidden
        return false;
    }


}