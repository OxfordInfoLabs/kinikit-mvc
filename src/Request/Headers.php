<?php


namespace Kinikit\MVC\Request;

use Kinikit\Core\Util\ObjectArrayUtils;

/**
 * Class Headers
 * @package Kinikit\MVC\Request
 * @noProxy
 */
class Headers {

    private $contentType;
    private $contentLength;
    private $acceptContentType;
    private $acceptCharset;
    private $acceptEncoding;
    private $acceptLanguage;
    private $connection;
    private $userAgent;

    // Custom headers
    private $customHeaders = [];


    public function __construct() {
        $this->parseCurrentRequest();
    }

    /**
     * @return mixed
     */
    public function getContentType() {
        return $this->contentType;
    }

    /**
     * @return mixed
     */
    public function getContentLength() {
        return $this->contentLength;
    }


    /**
     * @return mixed
     */
    public function getAcceptContentType() {
        return $this->acceptContentType;
    }

    /**
     * @return mixed
     */
    public function getAcceptCharset() {
        return $this->acceptCharset;
    }

    /**
     * @return mixed
     */
    public function getAcceptEncoding() {
        return $this->acceptEncoding;
    }

    /**
     * @return mixed
     */
    public function getAcceptLanguage() {
        return $this->acceptLanguage;
    }

    /**
     * @return mixed
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * @return mixed
     */
    public function getUserAgent() {
        return $this->userAgent;
    }


    /**
     * Get a custom header by name
     *
     * @param $name
     */
    public function getCustomHeader($name) {
        return $this->customHeaders[$name] ?? null;
    }


    // Populate from current request
    private function parseCurrentRequest() {

        $this->contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : (isset($_SERVER['HTTP_CONTENT_TYPE']) ? $_SERVER["HTTP_CONTENT_TYPE"] : null);
        $this->contentLength = isset($_SERVER['CONTENT_LENGTH']) ? $_SERVER['CONTENT_LENGTH'] : (isset($_SERVER['HTTP_CONTENT_LENGTH']) ? $_SERVER["HTTP_CONTENT_LENGTH"] : null);
        $this->acceptContentType = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : null;
        $this->acceptCharset = isset($_SERVER['HTTP_ACCEPT_CHARSET']) ? $_SERVER['HTTP_ACCEPT_CHARSET'] : null;
        $this->acceptEncoding = isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : null;;
        $this->acceptLanguage = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : null;;
        $this->connection = isset($_SERVER['HTTP_CONNECTION']) ? $_SERVER['HTTP_CONNECTION'] : null;;
        $this->userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;;

        // Grab any custom headers
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) == "HTTP_") {
                $header = substr($key, 5);
                $this->customHeaders[$header] = $value;
            }
        }


    }


}
