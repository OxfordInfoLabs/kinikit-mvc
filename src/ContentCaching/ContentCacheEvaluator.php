<?php

namespace Kinikit\MVC\ContentCaching;

use Kinikit\Core\Caching\CacheProvider;
use Kinikit\Core\Configuration\Configuration;

/**
 * Read / Write data to the cache if a cache value is set for a specific method.
 *
 * @package Kinikit\MVC\Framework\Caching
 */
class ContentCacheEvaluator {

    /**
     * @var CacheProvider
     */
    private CacheProvider $cache;


    const DEFAULT_CACHE_TIME = "1d";

    /**
     * Construct with default cache implementation.
     *
     * CacheEvaluator constructor.
     *
     * @param CacheProvider $cache
     */
    public function __construct($cache) {
        $this->cache = $cache;
    }


    /**
     * Get a cached result for current request using passed config.
     *
     * @param ContentCacheConfig $config
     * @param string $url
     */
    public function getCachedResult($config, $url) {;
        return $this->cache->get($url);
    }


    /**
     * Cache result using caching config
     *
     * @param ContentCacheConfig $config
     * @param string $url
     * @param mixed $result
     */
    public function cacheResult($config, $url, $result) {
        $this->cache->set($url, $result, $this->getCacheTime($config));
    }


    // Convert cache time
    private function getCacheTime(ContentCacheConfig $config): int {

        if ($config->getCacheTime()) {
            $cacheTime = $config->getCacheTime();
        } else if (Configuration::readParameter("content.cache.time")) {
            $cacheTime = Configuration::readParameter("content.cache.time");
        } else {
            $cacheTime = self::DEFAULT_CACHE_TIME;
        }

        $cacheTime = trim($cacheTime);


        if (is_numeric($cacheTime)) {
            return $cacheTime;
        }

        $period = substr($cacheTime, "-1");
        $cacheTime = substr($cacheTime, 0, -1);

        if (is_numeric($cacheTime)) {
            switch ($period) {
                case "h":
                    $cacheTime *= 60;
                    break;
                case "d":
                    $cacheTime = $cacheTime * 60 * 24;
                    break;
                case "y":
                    $cacheTime = $cacheTime * 60 * 24 * 365;
                    break;

            }
        }

        return $cacheTime;

    }

}
