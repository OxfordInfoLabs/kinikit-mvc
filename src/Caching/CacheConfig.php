<?php


namespace Kinikit\MVC\Caching;


class CacheConfig {

    /**
     * @var string
     */
    private $cache;

    /**
     * @var string
     */
    private $cacheTime;

    /**
     * CacheConfig constructor.
     * @param string $cache
     * @param string $cacheTime
     */
    public function __construct($cache = null, $cacheTime = null) {
        $this->cache = $cache;
        $this->cacheTime = $cacheTime;
    }


    /**
     * @return string
     */
    public function getCache() {
        return $this->cache;
    }

    /**
     * @param string $cache
     */
    public function setCache($cache) {
        $this->cache = $cache;
    }

    /**
     * @return string
     */
    public function getCacheTime() {
        return $this->cacheTime;
    }

    /**
     * @param string $cacheTime
     */
    public function setCacheTime($cacheTime) {
        $this->cacheTime = $cacheTime;
    }


}
