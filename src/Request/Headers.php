<?php


namespace Kinikit\MVC\Request;


class Headers {

    private $acceptContentType;
    private $acceptCharset;
    private $acceptEncoding;
    private $acceptLanguage;
    private $connection;
    private $userAgent;


    public function __construct() {
        $this->parseCurrentRequest();
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

    // Populate from current request
    private function parseCurrentRequest() {
        $this->acceptContentType = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : null;
        $this->acceptCharset = isset($_SERVER['HTTP_ACCEPT_CHARSET']) ? $_SERVER['HTTP_ACCEPT_CHARSET'] : null;
        $this->acceptEncoding = isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : null;;
        $this->acceptLanguage = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : null;;
        $this->connection = isset($_SERVER['HTTP_CONNECTION']) ? $_SERVER['HTTP_CONNECTION'] : null;;
        $this->userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;;
    }


}
