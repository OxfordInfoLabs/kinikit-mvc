<?php


namespace Kinikit\MVC\Response;


use Kinikit\MVC\ContentSource\ContentSource;

/**
 * Simple response which accepts a content source which can either be a ContentSource object or
 * simply a string to return.  The response code defaults to 200 but can be overridden
 *
 * @package Kinikit\MVC\Response
 */
class SimpleResponse extends Response {

    private $contentSource;

    /**
     * Construct with a content source and an optional response code (defaults to 200).
     * Custom headers can also be passed as key/value pairs which will be passed up to the parent
     *
     * @param ContentSource|string $contentSource
     * @param int $responseCode
     * @param string[string] $customHeaders
     */
    public function __construct($contentSource, $responseCode = 200, $customHeaders = []) {
        parent::__construct($responseCode, $customHeaders);
        $this->contentSource = ContentSource::resolveValueToSource($contentSource);

    }

    /**
     * Get the content source used for this response
     *
     * @return ContentSource
     */
    public function getContentSource() {
        return $this->contentSource;
    }

    /**
     * Return the content type.  This must be implemented by all Responses.
     *
     * @return string
     */
    public function getContentType() {
        return $this->contentSource->getContentType();
    }

    /**
     * Return the content length.  This must be implemented by all Responses but can return null
     * if no length header is to be added.
     *
     * @return integer
     */
    public function getContentLength() {
        return $this->contentSource->getContentLength();
    }

    /**
     * Echo the content directly to stdout.  This must be implemented by all Responses.
     *
     * @return mixed
     */
    public function streamContent() {
        $this->contentSource->streamContent();
    }
}
