<?php

namespace Kinikit\MVC\Framework\Caching;

/**
 * Interface for a cache provider
 *
 * @defaultImplementation Kinikit\MVC\Framework\Caching\HeadersOnlyCache
 *
 * Interface CacheProvider
 */
interface Cache {

    /**
     * Get the cached result of a method.
     *
     * Return a value if a cached value is to be returned or null if we need to revalidate.
     *
     * @param $controllerInstance
     * @param $methodName
     * @param $params
     * @param $classAnnotations
     * @return mixed
     */
    public function getCachedResult($controllerInstance, $methodName, $params, $maxAgeInMinutes);


    /**
     * Cache the result of a method for future performance.
     *
     * @param $controllerInstance
     * @param $methodName
     * @param $params
     * @param $classAnnotations
     * @return mixed
     */
    public function cacheResult($controllerInstance, $methodName, $params, $maxAgeInMinutes);


}
