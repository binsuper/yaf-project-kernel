<?php

namespace Gino\Yaf\Kernel;

use Gino\Phplib\ArrayObject;
use \Symfony\Component\HttpFoundation\Request as ActuallyRequest;

class Request {

    /** @var ActuallyRequest */
    protected $_operator;

    /** @var ArrayObject */
    public $v_query;

    /** @var ArrayObject */
    public $v_post;

    protected $_rawBody;

    /** @var ArrayObject */
    public $v_headers;

    /** @var ArrayObject */
    public $v_cookies;

    /** @var ArrayObject */
    public $v_servers;

    public function __construct() {
        $request = ActuallyRequest::createFromGlobals();

        $this->v_query  = ArrayObject::from($request->query->all());
        $this->v_post   = ArrayObject::from($request->request->all());
        $this->_rawBody = $request->getContent();
        $this->v_headers = ArrayObject::from(array_map(function ($v) {
            return $v[0] ?? '';
        }, $request->headers->all()));
        $this->v_cookies = ArrayObject::from($request->cookies->all());
        $this->v_servers = ArrayObject::from($request->server->all());

        if (strtolower($this->v_headers->get('content-type', '')) === 'application/json') {
            $this->v_post = ArrayObject::from(json_decode($this->_rawBody, true) ?: []);
        }

        $this->_operator = $request;
    }

    /**
     * @return string
     */
    public function getModuleName() {
        return \Yaf\Application::app()->getDispatcher()->getRequest()->getModuleName();
    }

    /**
     * @return string
     */
    public function getControllerName() {
        return \Yaf\Application::app()->getDispatcher()->getRequest()->getControllerName();
    }

    /**
     * @return string
     */
    public function getActionName() {
        return \Yaf\Application::app()->getDispatcher()->getRequest()->getActionName();
    }

    /**
     * @return ActuallyRequest
     */
    protected function operator(): ActuallyRequest {
        return $this->_operator;
    }

    /**
     * Set variables for query parameter
     *
     * @param array|string $key
     * @param null|mixed $val
     * @return $this
     */
    public function setQuery($key, $val = null) {
        $this->v_query->set($key, $val);
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
        return ArrayObject::from($this->v_post->toArray() + $this->v_query->toArray())->get($key, $def);
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
        return $this->v_query->get($key, $def);
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
        return $this->v_post->get($key, $def);
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
        return $this->v_headers->get($key, $def);
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
        return $this->v_servers->get($key, $def);
    }

    /**
     * @param mixed|array $key
     * @param mixed|null $def
     * @return mixed
     */
    public function cookie($key = null, $def = null) {
        return $this->v_cookies->get($key, $def);
    }

    /**
     * Returns the client IP address.
     *
     * @return string|null
     */
    public function getClientIp(): ?string {
        return $this->operator()->getClientIp();
    }

    /**
     * Returns the client IP addresses.
     *
     * @return array
     */
    public function getClientIps(): array {
        return $this->operator()->getClientIps();
    }

    /**
     * Returns the IP address of the remote client.
     *
     * @return string|null
     */
    public function getRemoteAddress(): ?string {
        return $this->getClientIp();
    }

    /**
     * Returns the host of the remote client.
     *
     * @return string|null
     */
    public function getRemoteHost(): ?string {
        return $this->server('REMOTE_HOST', $this->getRemoteAddress());
    }

    /**
     * Returns the host name.
     *
     * This method can read the client host name from the "X-Forwarded-Host" header
     * when trusted proxies were set via "setTrustedProxies()".
     *
     * The "X-Forwarded-Host" header must contain the client host name.
     *
     * @return string
     */
    public function getHost() {
        return $this->operator()->getHost();
    }

    /**
     * Returns the HTTP method with which the request was made (GET, POST, HEAD, PUT, ...).
     *
     * @return string
     */
    public function getMethod() {
        return $this->operator()->getRealMethod();
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
     * Returns the requested URI (path and query string).
     *
     * @return string The raw URI (i.e. not URI decoded)
     */
    public function getRequestUri(): string {
        return $this->operator()->getRequestUri();
    }

    /**
     * Returns the path being requested relative to the executed script.
     *
     * The path info always starts with a /.
     *
     * Suppose this request is instantiated from /mysite on localhost:
     *
     *  * http://localhost/mysite              returns an empty string
     *  * http://localhost/mysite/about        returns '/about'
     *  * http://localhost/mysite/enco%20ded   returns '/enco%20ded'
     *  * http://localhost/mysite/about?var=1  returns '/about'
     *
     * @return string The raw path (i.e. not urldecoded)
     */
    public function getRequestPath(): string {
        return $this->operator()->getPathInfo();
    }


}