<?php

namespace Gino\Yaf\Kernel\Database;

use Gino\Phplib\ArrayObject;
use Illuminate\Database\Capsule\Manager as Capsule;

class Manager extends Capsule {

    /**
     * 全局初始化
     *
     * @param array $options
     */
    public static function startup(array $options) {
        $db = new static();
        $db->setConnections($options);
        $db->setAsGlobal();
        $db->bootEloquent();
    }

    /**
     * 设置配置
     *
     * @param array $options
     */
    public function setConnections(array $options) {
        $array       = new ArrayObject($options);
        $default     = $array->get('default', false);
        $connections = $array->get('connections', []);

        foreach ($connections as $name => $config) {
            $this->addConnection($config, $name);
        }

        $default && isset($connections[$default]) && $this->addConnection($connections[$default]);
    }

}