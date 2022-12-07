<?php

namespace Gino\Yaf\Kernel\Exception;

use Gino\Yaf\Kernel\App;
use Gino\Yaf\Kernel\Log;

class ErrorHandler {

    /**
     * 日志实例
     *
     * @return \Monolog\Logger
     * @throws \Gino\Phplib\Error\BadConfigurationException
     */
    public static function logger() {
        return Log::channel();
    }

    /**
     * 错误处理
     */
    public static function error($error_no, $error_msg, $error_file, $error_line) {
        switch ($error_no) {
            case E_WARNING:
                // x / 0 错误 PHP7 依然不能很友好的自动捕获 只会产生 E_WARNING 级的错误
                // 捕获判断后 throw new DivisionByZeroError($error_msg)
                // 或者使用 intdiv(x, 0) 方法 会自动抛出 DivisionByZeroError 的错误
                if (strcmp('Division by zero', $error_msg) == 0) {
                    throw new \DivisionByZeroError($error_msg);
                }
                $level_tips = 'PHP Warning: ';
                break;
            case E_NOTICE:
                $level_tips = 'PHP Notice: ';
                break;
            case E_DEPRECATED:
                $level_tips = 'PHP Deprecated: ';
                break;
            case E_USER_ERROR:
                $level_tips = 'User Error: ';
                break;
            case E_USER_WARNING:
                $level_tips = 'User Warning: ';
                break;
            case E_USER_NOTICE:
                $level_tips = 'User Notice: ';
                break;
            case E_USER_DEPRECATED:
                $level_tips = 'User Deprecated: ';
                break;
            case E_STRICT:
                $level_tips = 'PHP Strict: ';
                break;
            default:
                $level_tips = 'Unkonw Type Error: ';
                break;
        }
        $error = $level_tips . $error_msg . ' in ' . $error_file . ' on ' . $error_line;
        static::logger()->error($error);
        return false;
    }

    /**
     * 异常处理
     */
    public static function exception(\Throwable $ex) {
        switch (get_class($ex)) {
            case \Yaf\Exception\LoadFailed\Controller::class:
            case \Yaf\Exception\LoadFailed\Module::class:
            case \Yaf\Exception\LoadFailed\Action::class:
            case MiddlewareBreakOff::class:
                static::logger()->debug($ex->getMessage());
                return;
        }
        static::record($ex);
    }

    /**
     * 记录异常日志
     */
    public static function record(\Throwable $ex) {
        $error = PHP_EOL . 'Error Type：' . get_class($ex) . PHP_EOL;
        $error .= 'Error Code：' . $ex->getCode() . PHP_EOL;
        $error .= 'Error Msg：' . $ex->getMessage() . PHP_EOL;
        $error .= 'Error File：' . $ex->getFile() . '(' . $ex->getLine() . ')' . PHP_EOL;
        $error .= 'Error Strace：' . $ex->getTraceAsString() . PHP_EOL;
        static::logger()->error($error);

        if ($ex->getPrevious()) {
            static::record($ex->getPrevious());
        }
    }

}