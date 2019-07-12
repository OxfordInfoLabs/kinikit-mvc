<?php

namespace Kinikit\MVC\Response;

use Kinikit\Core\DependencyInjection\Container;

/**
 * Base HTTP Response class.  This is an abstract class designed for extension
 * as required for specific types of concrete response.
 *
 * Class Response
 */
abstract class Response {

    /**
     * HTTP Response Code.
     *
     * @var integer
     */
    private $responseCode;


    /**
     * Headers static
     *
     * @var Headers
     */
    private $headers;


    // Common response constants used by the framework.
    const RESPONSE_SUCCESS = 200;
    const RESPONSE_REDIRECT_PERMANENT = 301;
    const RESPONSE_REDIRECT_TEMPORARY = 302;
    const RESPONSE_ACCESS_DENIED = 403;
    const RESPONSE_NOT_FOUND = 404;
    const RESPONSE_RATE_LIMITED = 429;
    const RESPONSE_GENERAL_ERROR = 500;


    /**
     * Constructor for a response - accepts simply a response code.
     *
     *
     * @param integer $responseCode
     * @param string $contentType
     */
    public function __construct($responseCode) {
        $this->responseCode = $responseCode;
        $this->headers = Container::instance()->get(Headers::class);
    }

    /**
     * Get the response code for this response.
     *
     * @return int
     */
    public function getResponseCode() {
        return $this->responseCode;
    }

    /**
     * Get the headers for this response.
     *
     * @return Headers
     */
    public function getHeaders() {
        return $this->headers;
    }


    /**
     * Set a response header.
     *
     * @param string $name
     * @param string $value
     */
    public function setHeader($name, $value) {
        $this->headers->set($name, $value);
    }


    /**
     * Send this response straight to stdout.  This effectively calls the abstract functions
     * below to set up headers etc before returning the response.
     */
    public function send($headersOnly = false) {

        // Set the response code.
        http_response_code($this->responseCode);

        $contentType = $this->getContentType();
        $contentLength = $this->getContentLength();

        // Set type and length headers if they have been returned.
        if ($contentType)
            $this->setHeader(Headers::HEADER_CONTENT_TYPE, $contentType);

        if ($contentLength)
            $this->setHeader(Headers::HEADER_CONTENT_LENGTH, $contentLength);

        // Stream content
        if (!$headersOnly)
            $this->streamContent();

    }


    /**
     * Return the content type.  This must be implemented by all Responses.
     *
     * @return string
     */
    public abstract function getContentType();


    /**
     * Return the content length.  This must be implemented by all Responses but can return null
     * if no length header is to be added.
     *
     * @return integer
     */
    public abstract function getContentLength();


    /**
     * Echo the content directly to stdout.  This must be implemented by all Responses.
     *
     * @return mixed
     */
    public abstract function streamContent();


}
