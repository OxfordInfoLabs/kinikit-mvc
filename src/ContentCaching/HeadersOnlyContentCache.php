<?php


namespace Kinikit\MVC\ContentCaching;


use Kinikit\Core\Util\Caching\CachingHeaders;
use Kinikit\MVC\Response\Headers;

/**
 * Default cache provider.  This does no local caching but simply adds headers to the response to cache.
 *
 * Class DefaultCacheProvider
 */
class HeadersOnlyContentCache implements ContentCache {

    /**
     * @var Headers
     */
    private $responseHeaders;


    /**
     * Construct with injected stuff
     *
     * HeadersOnlyCache constructor.
     *
     * @param Headers $responseHeaders
     */
    public function __construct($responseHeaders) {
        $this->responseHeaders = $responseHeaders;
    }


    /**
     * Get the cached result of a request URL.
     *
     * Return a value if a cached value is to be returned or null if we need to revalidate.
     *
     * @param $controllerInstance
     * @param $methodName
     * @param $params
     * @param $classAnnotations
     * @return mixed
     */
    public function getCachedResult($url, $maxAgeInMinutes) {
        return null;
    }


    /**
     * Cache the result of a request URL for future use.
     *
     * @param $controllerInstance
     * @param $methodName
     * @param $params
     * @param $classAnnotations
     * @return mixed
     */
    public function cacheResult($url, $maxAgeInMinutes, $result) {

        // Add cache control header if revalidate
        $numberOfSeconds = $maxAgeInMinutes * 60;
        $this->responseHeaders->set(Headers::HEADER_CACHE_CONTROL, "public, max-age=" . $numberOfSeconds . ", must-revalidate");
        $this->responseHeaders->set(Headers::HEADER_EXPIRES, gmdate("D, d M Y H:i:s", time() + $numberOfSeconds) . " GMT");
        $this->responseHeaders->set(Headers::HEADER_LAST_MODIFIED, gmdate("D, d M Y H:i:s", time()) . " GMT");

        $etag = '"' . filemtime(__FILE__) . '.' . date("U") . '"';
        $this->responseHeaders->set(Headers::HEADER_ETAG, $etag);

    }


}
