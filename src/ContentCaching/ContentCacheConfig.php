<?php


namespace Kinikit\MVC\ContentCaching;


class ContentCacheConfig {


    /**
     * @var string
     */
    private $cacheTime;

    /**
     * CacheConfig constructor.
     * @param string $cache
     * @param string $cacheTime
     */
    public function __construct($cacheTime = null) {
        $this->cacheTime = $cacheTime;
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
