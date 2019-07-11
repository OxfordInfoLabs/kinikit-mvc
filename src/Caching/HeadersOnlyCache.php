<?php


namespace Kinikit\MVC\Caching;


use Kinikit\Core\Util\Caching\CachingHeaders;

/**
 * Default cache provider.  This does no local caching but simply adds headers to the response to cache.
 *
 * Class DefaultCacheProvider
 */
class HeadersOnlyCache implements Cache {

    /**
     * Always return null here as we are not actually caching, just using headers.
     *
     * @param $controllerInstance
     * @param $methodName
     * @param $params
     * @param $classAnnotations
     * @return mixed
     */
    public function getCachedResult($controllerInstance, $methodName, $params, $maxAgeInMinutes) {
        return null;
    }

    /**
     * Add Caching headers for edge CDN / browser caching.
     *
     * @param $controllerInstance
     * @param $methodName
     * @param $params
     * @param $classAnnotations
     * @return mixed
     */
    public function cacheResult($controllerInstance, $methodName, $params, $maxAgeInMinutes) {
        CachingHeaders::instance()->addCachingHeaders($maxAgeInMinutes);
    }
}
