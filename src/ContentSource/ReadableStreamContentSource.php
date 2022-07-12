<?php


namespace Kinikit\MVC\ContentSource;


use Kinikit\Core\Stream\ReadableStream;

class ReadableStreamContentSource extends ContentSource {

    /**
     * @var ReadableStream
     */
    private $stream;

    /**
     * @var string
     */
    private $contentType;

    /**
     * Construct with
     *
     * ReadableStreamContentSource constructor.
     * @param $stream
     * @param string $contentType
     */
    public function __construct($stream, $contentType = "text/html") {
        $this->stream = $stream;
        $this->contentType = $contentType;
    }


    /**
     * Get the content type
     *
     * @return string
     */
    public function getContentType() {
        return $this->contentType;
    }

    /**
     * Get content length as -1
     *
     * @return int|void
     */
    public function getContentLength() {
        return -1;
    }

    /**
     * Stream content
     *
     * @return mixed|void
     */
    public function streamContent() {
        while (!$this->stream->isEof()) {
            $bytes = $this->stream->read(1024);
            print($bytes);
        }
    }
}