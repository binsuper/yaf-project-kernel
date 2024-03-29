<?php

namespace Gino\Yaf\Kernel;

use Symfony\Component\HttpFoundation\Response as ActuallyResponse;

class Response {

    /** HTTP 1.1 response code */
    public const
        S100_CONTINUE = 100,
        S101_SWITCHING_PROTOCOLS = 101,
        S102_PROCESSING = 102,
        S200_OK = 200,
        S201_CREATED = 201,
        S202_ACCEPTED = 202,
        S203_NON_AUTHORITATIVE_INFORMATION = 203,
        S204_NO_CONTENT = 204,
        S205_RESET_CONTENT = 205,
        S206_PARTIAL_CONTENT = 206,
        S207_MULTI_STATUS = 207,
        S208_ALREADY_REPORTED = 208,
        S226_IM_USED = 226,
        S300_MULTIPLE_CHOICES = 300,
        S301_MOVED_PERMANENTLY = 301,
        S302_FOUND = 302,
        S303_SEE_OTHER = 303,
        S303_POST_GET = 303,
        S304_NOT_MODIFIED = 304,
        S305_USE_PROXY = 305,
        S307_TEMPORARY_REDIRECT = 307,
        S308_PERMANENT_REDIRECT = 308,
        S400_BAD_REQUEST = 400,
        S401_UNAUTHORIZED = 401,
        S402_PAYMENT_REQUIRED = 402,
        S403_FORBIDDEN = 403,
        S404_NOT_FOUND = 404,
        S405_METHOD_NOT_ALLOWED = 405,
        S406_NOT_ACCEPTABLE = 406,
        S407_PROXY_AUTHENTICATION_REQUIRED = 407,
        S408_REQUEST_TIMEOUT = 408,
        S409_CONFLICT = 409,
        S410_GONE = 410,
        S411_LENGTH_REQUIRED = 411,
        S412_PRECONDITION_FAILED = 412,
        S413_REQUEST_ENTITY_TOO_LARGE = 413,
        S414_REQUEST_URI_TOO_LONG = 414,
        S415_UNSUPPORTED_MEDIA_TYPE = 415,
        S416_REQUESTED_RANGE_NOT_SATISFIABLE = 416,
        S417_EXPECTATION_FAILED = 417,
        S421_MISDIRECTED_REQUEST = 421,
        S422_UNPROCESSABLE_ENTITY = 422,
        S423_LOCKED = 423,
        S424_FAILED_DEPENDENCY = 424,
        S426_UPGRADE_REQUIRED = 426,
        S428_PRECONDITION_REQUIRED = 428,
        S429_TOO_MANY_REQUESTS = 429,
        S431_REQUEST_HEADER_FIELDS_TOO_LARGE = 431,
        S451_UNAVAILABLE_FOR_LEGAL_REASONS = 451,
        S500_INTERNAL_SERVER_ERROR = 500,
        S501_NOT_IMPLEMENTED = 501,
        S502_BAD_GATEWAY = 502,
        S503_SERVICE_UNAVAILABLE = 503,
        S504_GATEWAY_TIMEOUT = 504,
        S505_HTTP_VERSION_NOT_SUPPORTED = 505,
        S506_VARIANT_ALSO_NEGOTIATES = 506,
        S507_INSUFFICIENT_STORAGE = 507,
        S508_LOOP_DETECTED = 508,
        S510_NOT_EXTENDED = 510,
        S511_NETWORK_AUTHENTICATION_REQUIRED = 511;

    public const REASON_PHRASES = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    /** @var ActuallyResponse */
    protected $_operator;

    protected $body = '';

    public function __construct() {
        $this->_operator = new ActuallyResponse();
    }

    /**
     * @return ActuallyResponse
     */
    public function operator() {
        return $this->_operator;
    }

    /**
     * 设置http响应状态码
     *
     * @param int $code
     * @param string|null $reason
     * @return $this
     */
    public function setCode(int $code, ?string $reason = null) {
        $this->operator()->setStatusCode($code, $reason ?: (static::REASON_PHRASES[$code] ?? null));
        return $this;
    }

    /**
     * 返回http响应状态码
     */
    public function getCode(): int {
        return $this->operator()->getStatusCode();
    }

    /**
     * @param string $name
     * @param string|null $val
     * @return $this
     */
    public function setHeader(string $name, ?string $val = null) {
        $this->operator()->headers->set($name, $val);
        return $this;
    }

    /**
     * @param string|null $name
     * @param null $def
     * @return array|mixed
     */
    public function getHeader(?string $name = null, $def = null) {
        if ($name === null) {
            return $this->operator()->headers->all();
        } else {
            return $this->operator()->headers->get($name, $def);
        }
    }

    /**
     * 设置body
     *
     * @param string $body
     * @return $this
     */
    public function setBody($body) {
        $this->operator()->setContent($body);
        return $this;
    }

    /**
     * 返回body
     *
     * @return string
     */
    public function getBody() {
        return $this->operator()->getContent() ?: '';
    }


    /**
     * Sends HTTP headers and content.
     */
    public function display() {
        $this->operator()->send();
    }

    /**
     * 输出
     */
    public function flush() {
        $this->display();
    }

}