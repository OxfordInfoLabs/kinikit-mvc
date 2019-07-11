<?php

namespace Kinikit\MVC\Framework\HTTP;

/**
 * URL Helper class
 *
 */
class URLHelper {

    private $segments;
    private $url;
    private $requestURI;
    private static $testURL = null;

    /**
     * Only useful constructor.  Takes a full url
     *
     * @param string $url
     * @return URLHelper
     */
    public function __construct($url) {
        // Process either this url or the test url if one was set.
        $this->processURL($url);
    }

    /**
     * Convenient static accessor for obtaining a url helper instance for the current
     * URL (got from $_SERVER['REQUEST_URI']).  NB if a test url has been set this will
     * be used instead.
     *
     * @return URLHelper
     */
    public static function getCurrentURLInstance() {
        return new URLHelper (URLHelper::$testURL == null ? $_SERVER ['REQUEST_URI'] : URLHelper::$testURL);
    }


    /**
     * Return the current full host string for the current request including protocol and ports etc.
     */
    public static function getCurrentFullHostString() {

        $hostString = $_SERVER["HTTPS"] ? "https" : "http";
        $hostString .= "://";
        $hostString .= $_SERVER["HTTP_HOST"];
        $hostString .= $_SERVER["SERVER_PORT"] != 80 ? ":" . $_SERVER["SERVER_PORT"] : "";

        return $hostString;


    }


    /**
     * Set a test url if we need to force behaviour upon this class.
     *
     * @param unknown_type $testURL
     */
    public static function setTestURL($testURL) {
        URLHelper::$testURL = $testURL;
    }

    /**
     * Unset the test url.
     *
     */
    public static function unsetTestURL() {
        URLHelper::$testURL = null;
    }

    /**
     * Get the full url (used to construct this instance)s
     *
     * @return unknown
     */
    public function getURL() {
        return $this->url;
    }

    /**
     * Get a partial string version of this url object starting from a particular segment
     *
     * @return string
     */
    public function getPartialURLFromSegment($segmentIdx) {

        $partialURL = "";

        for ($i = $segmentIdx; $i < sizeof($this->segments); $i++) {
            $partialURL .= (($i > $segmentIdx) ? "/" : "") . $this->segments [$i];
        }

        return $partialURL;
    }

    /**
     * Get a url segment by index
     * eg. for url http://www.google.com/home/3
     * 'www.google.com' would be segment 0, 'home' would be segment 1 and '3' would be segment 2
     * @param number $index
     */
    public function getSegment($index) {
        return $this->segments [$index];
    }

    /**
     * Get the number of segments
     *
     * @return unknown
     */
    public function getSegmentCount() {
        return sizeof($this->segments);
    }

    /**
     * Get the first segment
     *
     * @return unknown
     */
    public function getFirstSegment() {
        return $this->segments [0];
    }

    /**
     * Get the last segments
     *
     * @return unknown
     */
    public function getLastSegment() {
        return $this->segments [sizeof($this->segments) - 1];
    }

    /**
     * Get the array of all segments
     *
     */
    public function getAllSegments() {
        return $this->segments;
    }

    /**
     * Get the request path without the query string.
     *
     */
    public function getRequestPath() {
        $splitPath = explode("?", $this->requestURI);
        return $splitPath [0];
    }

    /**
     * Get the query string or a blank string if no query params
     *
     */
    public function getQueryString() {
        $queryParams = explode("?", $this->url);
        return sizeof($queryParams) > 1 ? "?" . $queryParams [1] : "";
    }

    /**
     * Get all query parameters contained within this URL as an associative array.
     *
     * @return array
     */
    public function getQueryParametersArray() {
        $queryString = $this->getQueryString();

        if (strlen($queryString) == 0) return array();

        $splitQuery = explode("&", substr($queryString, 1));
        $returnedParams = array();
        foreach ($splitQuery as $param) {
            $splitParam = explode("=", $param);
            if (sizeof($splitParam) == 2) $returnedParams [$splitParam [0]] = $splitParam [1];
        }

        return $returnedParams;

    }

    // Process a url, splitting it into it's constituent parts
    // Initially for expediency we will simply split up the URL segments
    // but eventually when we come to revamp search etc. we may need to split
    // off query parameters too.
    private function processURL($url) {

        // Clean up special characters in url params
        $url = str_replace("%20", " ", $url);

        // Store a private copy for use later
        $this->url = URLHelper::$testURL == null ? $url : URLHelper::$testURL;

        // Split it on protocol first if required.
        if (substr($url, 0, 4) == "http") {
            $splitProtocol = explode("://", $url);
            $requestSection = sizeof($splitProtocol) > 1 ? $splitProtocol [1] : $splitProtocol [0];
            $requestSection = substr($requestSection, strpos($requestSection, "/"));
        } else {
            $requestSection = $url;
        }
        $this->requestURI = $requestSection;

        // Now split it on question mark for query if required.
        $splitQuestionMark = explode("?", $requestSection);
        $queryStripped = $splitQuestionMark [0];

        // Now ensure no trailing or leading slash.
        if (substr($queryStripped, 0, 1) == '/') $queryStripped = substr($queryStripped, 1);
        if (substr($queryStripped, strlen($queryStripped) - 1) == '/') $queryStripped = substr($queryStripped, 0, strlen($queryStripped) - 1);

        // Now simply set the segments array to be the split on "/"
        $this->segments = explode("/", $queryStripped);

    }

}

?>
