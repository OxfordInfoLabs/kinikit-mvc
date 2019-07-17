<?php

namespace Kinikit\MVC\ContentCaching;

use Kinikit\Core\Configuration\Configuration;
use Kinikit\Core\DependencyInjection\Container;
use Kinikit\Core\Util\Annotation\ClassAnnotations;
use Kinikit\MVC\Request\Request;

/**
 * Read / Write data to the cache if a cache value is set for a specific method.
 *
 * @package Kinikit\MVC\Framework\Caching
 */
class ContentCacheEvaluator {

    /**
     * @var ContentCache
     */
    private $cache;


    const DEFAULT_CACHE_TIME = "1d";

    /**
     * Construct with default cache implementation.
     *
     * CacheEvaluator constructor.
     *
     * @param ContentCache $cache
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
    public function getCachedResult($config, $url) {
        return $this->cache->getCachedResult($url, $this->getCacheTime($config));
    }


    /**
     * Cache result using caching config
     *
     * @param ContentCacheConfig $config
     * @param string $url
     * @param mixed $result
     */
    public function cacheResult($config, $url, $result) {

        $this->cache->cacheResult($url, $this->getCacheTime($config), $result);

    }


    // Convert cache time
    private function getCacheTime($config) {


        if ($config->getCacheTime())
            $cacheTime = $config->getCacheTime();
        else if (Configuration::readParameter("content.cache.time")) {
            $cacheTime = Configuration::readParameter("content.cache.time");
        } else {
            $cacheTime = self::DEFAULT_CACHE_TIME;
        }

        $cacheTime = trim($cacheTime);


        if (is_numeric($cacheTime)) return $cacheTime;
        else {
            $period = substr($cacheTime, "-1");
            $cacheTime = substr($cacheTime, 0, strlen($cacheTime) - 1);

            if (is_numeric($cacheTime)) {
                switch ($period) {
                    case "h":
                        $cacheTime = $cacheTime * 60;
                        break;
                    case "d":
                        $cacheTime = $cacheTime * 60 * 24;
                        break;
                    case "y":
                        $cacheTime = $cacheTime * 60 * 24 * 365;
                        break;

                }
            }

        }

        return $cacheTime;

    }

}
