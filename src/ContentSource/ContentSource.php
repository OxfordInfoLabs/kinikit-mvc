<?php

namespace Kinikit\MVC\ContentSource;

/**
 * Generic Content source interface.  Allows for an abstracted method of getting content
 * for e.g. streaming in Response objects.  This simply expects three methods for
 *
 * Interface ContentSource
 */
interface ContentSource {

    /**
     * Return the content type.
     *
     * @return string
     */
    public function getContentType();


    /**
     * Return the content length.
     *
     * @return integer
     */
    public function getContentLength();


    /**
     * Echo the content directly to stdout.
     *
     * @return mixed
     */
    public function streamContent();


}
