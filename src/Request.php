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
        return $this->input();
    }

    /**
     * Returns variable provided to the script via URL query ($_GET) and POST method ($_POST).
     *
     * @param mixed $key
     * @param mixed|null $def
     * @return array|ArrayObject|mixed|null
     */
    public function input($key = null, $def = null) {
        return ArrayObject::from($this->post() + $this->query())->get($key, $def);
    }

    /**
     * Returns variable provided to the script via URL query ($_GET).
     * If no key is passed, returns the entire array.
     *
     * @param mixed|null $key
     * @param mixed $def
     * @return mixed
     */
    public function query($key = null, $def = null) {
        return ArrayObject::from($this->handler()->getQuery())->get($key, $def);
    }

    /**
     * Returns variable provided to the script via POST method ($_POST).
     * If no key is passed, returns the entire array.
     *
     * @param mixed|null $key
     * @param mixed $def
     * @return mixed
     */
    public function post($key = null, $def = null) {
        return ArrayObject::from($this->handler()->getPost())->get($key, $def);
    }

    /**
     * Return variable provided to the script via which is json string of HTTP request body.
     * If no key is passed, returns the entire array.
     *
     * @param mixed|null $key
     * @param mixed $def
     * @return mixed|null
     */
    public function json($key = null, $def = null) {
        if (!isset($this->_json_data)) {
            $this->_json_data = json_decode($this->rawBody(), true);
        }
        if (!$this->_json_data) {
            return $def;
        }
        return ArrayObject::from($this->_json_data)->get($key, $def);
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
     * @param mixed|array $key
     * @param mixed|null $def
     * @return mixed
     */
    public function header($key = null, $def = null) {
        if (is_string($key)) {
            $key = strtolower($key);
        } else if (is_array($key)) {
            array_walk($key, function (&$v) {
                $v = strtolower($v);
            });
        }
        return ArrayObject::from($this->handler()->getHeaders())->get($key, $def);
    }

    /**
     * Returns all HTTP headers as associative array.
     *
     * @return array
     */
    public function headers() {
        return $this->header();
    }

    /**
     * @param mixed|array $key
     * @param mixed|null $def
     * @return mixed
     */
    public function server($key = null, $def = null) {
        return $this->header($key, $def);
    }

    /**
     * @param mixed|array $key
     * @param mixed|null $def
     * @return mixed
     */
    public function cookie($key = null, $def = null) {
        return ArrayObject::from($this->handler()->getCookies())->get($key, $def);
    }

    /**
     * Returns the IP address of the remote client.
     *
     * @return string|null
     */
    public function getClientIp() {
        return $this->handler()->getRemoteAddress();
    }

    /**
     * Returns the HTTP method with which the request was made (GET, POST, HEAD, PUT, ...).
     *
     * @return string
     */
    public function getMethod() {
        return $this->handler()->getMethod();
    }

    /**
     * Is it an AJAX request?
     *
     * @return bool
     */
    public function isAjax(): bool {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * Returns the host of the remote client.
     *
     * @return string|null
     */
    public function getRemoteHost(): ?string {
        return $this->handler()->getRemoteHost();
    }

}