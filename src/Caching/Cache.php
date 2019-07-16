<?php

namespace Kinikit\MVC\Caching;

/**
 * Interface for a cache provider
 *
 * @defaultImplementation Kinikit\MVC\Caching\HeadersOnlyCache
 *
 * Interface CacheProvider
 */
interface Cache {

    /**
     * Get the cached result of a request URL.
     *
     * Return a value if a cached value is to be returned or null if we need to revalidate.
     *
     * @param string $url
     * @param int $maxAgeInMinutes
     *
     * @return mixed
     */
    public function getCachedResult($url, $maxAgeInMinutes);


    /**
     * Cache the result of a request URL for future use.
     *
     * @param string $url
     * @param int $maxAgeInMinutes
     * @param mixed $result
     *
     */
    public function cacheResult($url, $maxAgeInMinutes, $result);


}
