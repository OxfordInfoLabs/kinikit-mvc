<?php


namespace Kinikit\MVC\ContentSource;

/**
 * Simple content source where the content is supplied as a string.
 * The content type defaults to text/html but can be overridden and the size is auto derived.
 */
class StringContentSource implements ContentSource {

    /**
     * @var string
     */
    private $contentString;

    /**
     * @var string
     */
    private $contentType;

    /**
     *
     * Construct
     *
     * StringContentSource constructor.
     */
    public function __construct($contentString, $contentType = "text/html") {
        $this->contentString = $contentString;
        $this->contentType = $contentType;
    }

    /**
     * Return the content type
     *
     * @return string
     */
    public function getContentType() {
        return $this->contentType;
    }

    /**
     * Return the content length.
     *
     * @return integer
     */
    public function getContentLength() {
        return strlen($this->contentString);
    }

    /**
     * Echo the content directly to stdout.
     *
     * @return mixed
     */
    public function streamContent() {
        echo $this->contentString;
    }
}
