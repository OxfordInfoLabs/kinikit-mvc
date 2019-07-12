<?php

namespace Kinikit\MVC\ContentSource;

use Kinikit\Core\Exception\WrongParametersException;

/**
 * Generic Content source interface.  Allows for an abstracted method of getting content
 * for e.g. streaming in Response objects.  This simply expects three methods for
 *
 * Interface ContentSource
 */
abstract class ContentSource {

    /**
     * Return the content type.
     *
     * @return string
     */
    abstract public function getContentType();


    /**
     * Return the content length.
     *
     * @return integer
     */
    abstract public function getContentLength();


    /**
     * Echo the content directly to stdout.
     *
     * @return mixed
     */
    abstract public function streamContent();


    /**
     * Resolve a value to a source.  This helper essentially
     * copes with an explicit string and maps to a string source
     * and throws if the value is not a content source otherwise
     *
     * @param mixed $source
     */
    public static function resolveValueToSource($source) {
        if (is_string($source)) {
            return new StringContentSource($source);
        } else if ($source instanceof ContentSource) {
            return $source;
        } else {
            throw new WrongParametersException("Content source must be either a string or a ContentSource instance");
        }
    }

}
