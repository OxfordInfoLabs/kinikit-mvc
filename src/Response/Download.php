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
     * Construct with source and target filename and optional response code.
     *
     * @param ContentSource|string $contentSource
     * @param string $targetFilename
     * @param int $responseCode
     */
    public function __construct($contentSource, $targetFilename, $responseCode = 200) {
        parent::__construct($contentSource, $responseCode);
        $this->setHeader(Headers::HEADER_CONTENT_DISPOSITION, 'attachment; filename="' . $targetFilename . '"');
    }
}
