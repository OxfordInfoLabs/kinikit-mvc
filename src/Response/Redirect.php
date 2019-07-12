<?php


namespace Kinikit\MVC\Response;


/**
 * Implement a redirect as convenience response
 *
 * @package Kinikit\MVC\Response
 */
class Redirect extends Response {


    /**
     * Construct with a redirect URL and an optional permanent
     * flag (defaults to true) which toggles between 301 and 302 accordingly.
     *
     * @param $redirectURL
     * @param bool $permanent
     */
    public function __construct($redirectURL, $permanent = true) {
        parent::__construct($permanent ? self::RESPONSE_REDIRECT_PERMANENT : self::RESPONSE_REDIRECT_TEMPORARY);
        $this->setHeader(Headers::HEADER_LOCATION, $redirectURL);
    }

    /**
     * Return no content type for a redirect.
     *
     * @return string
     */
    public function getContentType() {
        return null;
    }

    /**
     * Don't return a content length for a redirection.
     *
     * @return integer
     */
    public function getContentLength() {
        return null;
    }

    /**
     * No content required for a redirect.
     *
     * @return mixed
     */
    public function streamContent() {
    }
}
