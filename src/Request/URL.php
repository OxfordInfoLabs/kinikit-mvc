<?php

namespace Kinikit\MVC\Request;


/**
 * URL class - constructed with a full URL string.  This provides convenient methods for
 * getting e.g. URL segments and other items of interest from the string.
 *
 * Class URL
 */
class URL {

    private $protocol;
    private $host;
    private $port;
    private $pathSegments = array();
    private $queryParameters = array();

    const PROTOCOL_HTTP = "HTTP";
    const PROTOCOL_HTTPS = "HTTPS";


    /**
     * Constructed with a url string.
     *
     * URL constructor.
     * @param $url
     */
    public function __construct($url) {
        $this->process($url);
    }


    /**
     * Get the protocol for this URL (usually HTTP or HTTPS)
     *
     * @return string
     */
    public function getProtocol() {
        return $this->protocol;
    }

    /**
     * @return mixed
     */
    public function getHost() {
        return $this->host;
    }


    /**
     * Get the port for this URL (default to 80 or 443 if protocol is HTTP or HTTPS accordingly).
     *
     * @return int
     */
    public function getPort() {
        return $this->port;
    }


    /**
     * Get a boolean indicating whether or not this request is secure (HTTPS) or not.
     *
     * @return bool
     */
    public function isSecure() {
        return $this->protocol == self::PROTOCOL_HTTPS;
    }


    /**
     * Get the request path without leading /
     */
    public function getPath($includeQueryParams = false) {
        $path = join("/", $this->pathSegments);
        if ($includeQueryParams && $this->queryParameters) {
            $path .= "?" . join("&", $this->queryParameters);
        }
        return $path;
    }


    /**
     * Get the total number of path segments
     *
     * @return int
     */
    public function getPathSegmentCount() {
        return sizeof($this->pathSegments);
    }

    /**
     * Get all path segments
     *
     * @return string[]
     */
    public function getPathSegments() {
        return $this->pathSegments;
    }


    /**
     * Get a path segment by index
     *
     * @param int $index
     * @return string
     */
    public function getPathSegment($index) {
        return $index >= 0 && $index < sizeof($this->pathSegments) ? $this->pathSegments[$index] : null;
    }


    /**
     * Get the first path segment
     *
     * @return string
     */
    public function getFirstPathSegment() {
        return $this->getPathSegment(0);
    }


    /**
     * Get the last path segment
     *
     * @return string
     */
    public function getLastPathSegment() {
        return $this->getPathSegment(sizeof($this->pathSegments) - 1);
    }


    /**
     * Get the query parameters as associative array indexed by key.
     *
     * @return string[string]
     */
    public function getQueryParameters() {
        return $this->queryParameters;
    }

    /**
     * Get a single query parameter by key.
     *
     * @return string
     */
    public function getQueryParameter($key) {
        return isset($this->queryParameters[$key]) ? $this->queryParameters[$key] : null;
    }


    /**
     * Process this url
     */
    private function process($url) {

        preg_match("/^(https*):\/\/([^:\/]+)(:*[0-9]*)([^\?]*)(\?*.*)$/", $url, $matches);
        
        if (sizeof($matches) > 1) {
            $this->protocol = strtoupper($matches[1]);
        }
        if (sizeof($matches) > 2) {
            $this->host = $matches[2];
        }

        if (sizeof($matches) > 3 && $matches[3]) {
            $this->port = ltrim($matches[3], ":");
        } else {
            $this->port = $this->protocol == self::PROTOCOL_HTTP ? 80 : ($this->protocol == self::PROTOCOL_HTTPS ? 443 : null);
        }

        if (sizeof($matches) > 4) {
            $this->pathSegments = explode("/", ltrim($matches[4], "/"));
        }


        if (sizeof($matches) > 5) {
            $rawParams = explode("&", ltrim($matches[5], "?"));
            foreach ($rawParams as $param) {
                $exploded = explode("=", $param);
                if (sizeof($exploded) == 2)
                    $this->queryParameters[$exploded[0]] = $exploded[1];
            }
        }
    }


}
