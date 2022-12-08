<?php

namespace Gino\Yaf\Kernel;

use Gino\Phplib\ArrayObject;
use Nette\Http\RequestFactory;

class Request {

    /** @var \Nette\Http\Request */
    protected $_handler;

    /** @var ArrayObject */
    protected $_query;

    /** @var ArrayObject */
    protected $_post;

    protected $_rawBody;

    /** @var ArrayObject */
    protected $_headers;

    /** @var ArrayObject */
    protected $_cookies;

    public function __construct() {
        $factory        = new RequestFactory();
        $this->_handler = $factory->fromGlobals();

        $this->_query   = ArrayObject::from($this->_handler->getQuery());
        $this->_post    = ArrayObject::from($this->_handler->getPost());
        $this->_rawBody = $this->_handler->getRawBody();
        $this->_headers = ArrayObject::from($this->_handler->getHeaders());
        $this->_cookies = ArrayObject::from($this->_handler->getCookies());

        if (strtolower($this->_headers->get('content-type', '')) === 'application/json') {
            $this->_post = ArrayObject::from(json_decode($this->_rawBody, true) ?: []);
        }
    }

    /**
     * @return \Nette\Http\Request
     */
    protected function handler() {
        return $this->_handler;
    }

    /**
     * Set variables for query parameter
     *
     * @param array|string $key
     * @param null|mixed $val
     * @return $this
     */
    public function setQuery($key, $val = null) {
        $this->_query->set($key, $val);
        return $this;
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
        return ArrayObject::from($this->_post->toArray() + $this->_query->toArray())->get($key, $def);
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
        return $this->_query->get($key, $def);
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
        return $this->_post->get($key, $def);
    }

    /**
     * Return raw content of HTTP request body.
     *
     * @return string
     */
    public function rawBody(): string {
        return $this->_rawBody;
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
        return $this->_headers->get($key, $def);
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
        return $this->_cookies->get($key, $def);
    }

    /**
     * Returns the IP address of the remote client.
     *
     * @return string|null
     */
    public function getRemoteAddress(): ?string {
        return $this->handler()->getRemoteAddress();
    }

    /**
     * Returns the host of the remote client.
     *
     * @return string|null
     */
    public function getRemoteHost(): ?string {
        return $this->handler()->getRemoteHost();
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
     * @return string
     */
    public function getRequestUri(): string {
        return $this->handler()->getUrl()->getPath();
    }


}