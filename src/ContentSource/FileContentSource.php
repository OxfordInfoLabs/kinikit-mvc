<?php


namespace Kinikit\MVC\ContentSource;


use Kinikit\Core\Configuration\FileResolver;
use Kinikit\Core\DependencyInjection\Container;
use Kinikit\Core\Exception\FileNotFoundException;

/**
 * Content source implementation where the content is stored in a file on
 * the server.  Content type and length are both inferred from the file.
 *
 * @package Kinikit\MVC\ContentSource
 */
class FileContentSource extends ContentSource {

    private $filepath;

    /**
     * Construct with file path to file
     *
     * @param $filepath
     */
    public function __construct($filepath) {
        if (!file_exists($filepath)) {
            $fileResolver = Container::instance()->get(FileResolver::class);
            $resolvedFilePath = $fileResolver->resolveFile($filepath);
            if (!$resolvedFilePath)
                throw new FileNotFoundException($filepath);
            else
                $filepath = $resolvedFilePath;
        }

        $this->filepath = $filepath;
    }


    /**
     * Get the resolved file path for this source.
     *
     * @return mixed
     */
    public function getFilePath() {
        return $this->filepath;
    }

    /**
     * Return the content type.
     *
     * @return string
     */
    public function getContentType() {
        return mime_content_type($this->filepath);
    }

    /**
     * Return the content length.
     *
     * @return integer
     */
    public function getContentLength() {
        return filesize($this->filepath);
    }

    /**
     * Echo the content directly to stdout.
     *
     * @return mixed
     */
    public function streamContent() {
        readfile($this->filepath);
    }
}
