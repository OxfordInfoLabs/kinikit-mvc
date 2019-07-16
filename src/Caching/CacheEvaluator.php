<?php

namespace Kinikit\MVC\Caching;

use Kinikit\Core\DependencyInjection\Container;
use Kinikit\Core\Util\Annotation\ClassAnnotations;
use Kinikit\MVC\Request\Request;

/**
 * Read / Write data to the cache if a cache value is set for a specific method.
 *
 * @package Kinikit\MVC\Framework\Caching
 */
class CacheEvaluator {

    /**
     * @var Cache
     */
    private $defaultCache;

    /**
     * Construct with default cache implementation.
     *
     * CacheEvaluator constructor.
     *
     * @param Cache $defaultCache
     */
    public function __construct($defaultCache) {
        $this->defaultCache = $defaultCache;
    }


    /**
     * Get a cached result for current request using passed config.
     *
     * @param CacheConfig $config
     * @param string $url
     */
    public function getCachedResult($config, $url) {
        return $this->getCache($config)->getCachedResult($url, $this->convertCacheTime($config->getCacheTime()));
    }


    /**
     * Cache result using caching config
     *
     * @param CacheConfig $config
     * @param string $url
     * @param mixed $result
     */
    public function cacheResult($config, $url, $result) {

        $this->getCache($config)->cacheResult($url, $this->convertCacheTime($config->getCacheTime()), $result);

    }


    /**
     * Get the cache from the config
     *
     * @param CacheConfig $config
     *
     * @return Cache
     */
    private function getCache($config) {
        if ($config->getCache()) {
            return Container::instance()->get($config->getCache());
        } else {
            return $this->defaultCache;
        }
    }


    // Convert cache time
    private function convertCacheTime($cacheTime) {

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
