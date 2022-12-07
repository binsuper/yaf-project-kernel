<?php

namespace Gino\Yaf\Kernel;

use Gino\Phplib\ArrayObject;
use Nette\Http\RequestFactory;

class Request {

    /** @var \Nette\Http\Request */
    protected $_handler;

    public function __construct() {
        $factory        = new RequestFactory();
        $this->_handler = $factory->fromGlobals();
    }

    /**
     * @return \Nette\Http\Request
     */
    public function handler() {
        return $this->_handler;
    }

    /**
     * Returns variable provided to the script via URL query ($_GET) and POST method ($_POST).
     *
     * @return array
     */
    public function all() {
        return $this->postGet();
    }

    /**
     * Returns variable provided to the script via URL query ($_GET) and POST method ($_POST).
     *
     * @return array
     */
    public function postGet() {
        return array_merge($this->get(), $this->post());
    }

    /**
     * Returns variable provided to the script via URL query ($_GET).
     * If no key is passed, returns the entire array.
     *
     * @param string|null $key
     * @param null $def
     * @return mixed
     */
    public function get(?string $key = null, $def = null) {
        if (is_null($key)) {
            return $this->_handler->getQuery();
        }
        $v = $this->_handler->getQuery($key);
        return is_null($v) ? $def : $v;
    }

    /**
     * Returns variable provided to the script via POST method ($_POST).
     * If no key is passed, returns the entire array.
     *
     * @param string|null $key
     * @return mixed
     */
    public function post(?string $key = null, $def = null) {
        if (is_null($key)) {
            return $this->_handler->getPost();
        }
        $v = $this->_handler->getPost($key);
        return is_null($v) ? $def : $v;
    }

    /**
     * Return variable provided to the script via which is json string of HTTP request body.
     * If no key is passed, returns the entire array.
     *
     * @param string|null $key
     * @param null $def
     * @return mixed|null
     */
    public function json(?string $key = null, $def = null) {
        if (!isset($this->_json_data)) {
            $this->_json_data = json_decode($this->rawBody(), true);
        }
        if (!$this->_json_data) {
            return $def;
        }
        if (is_null($key)) {
            return $this->_json_data;
        }
        return $this->_json_data[$key] ?? $def;
    }

    /**
     * Return raw content of HTTP request body.
     *
     * @return string
     */
    public function rawBody(): string {
        return $this->_handler->getRawBody();
    }

    /**
     * Returns an HTTP header or `null` if it does not exist. The parameter is case-insensitive.
     *
     * @param string $key
     * @return string|null
     */
    public function header(string $key) {
        return $this->handler()->getHeader($key);
    }

    /**
     * Returns all HTTP headers as associative array.
     *
     * @return array
     */
    public function headers(): array {
        return $this->handler()->getHeaders();
    }

}