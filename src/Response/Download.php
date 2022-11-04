<?php


namespace Kinikit\MVC\Response;

use Kinikit\MVC\ContentSource\ContentSource;

/**
 * Download response.  Provides a convenience wrapper to
 * set headers etc for a file download.
 *
 * Class Download
 * @package Kinikit\MVC\Response
 */
class Download extends SimpleResponse {

    /**
     * @var string
     */
    private $targetFilename;

    /**
     * Construct with source and target filename and optional response code.
     *
     * @param ContentSource|string $contentSource
     * @param string $targetFilename
     * @param int $responseCode
     */
    public function __construct($contentSource, $targetFilename, $responseCode = 200, $customHeaders = []) {
        parent::__construct($contentSource, $responseCode, $customHeaders);
        $this->targetFilename = $targetFilename;

    }

    /**
     * Inject header for download before processing core functionality.
     *
     * @return mixed|void
     */
    public function streamContent() {
        $this->setHeader(Headers::HEADER_CONTENT_DISPOSITION, 'attachment; filename="' . $this->targetFilename . '"');
        parent::streamContent();
    }


}
