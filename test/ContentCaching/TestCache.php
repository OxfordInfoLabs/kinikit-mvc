<?php


namespace Kinikit\MVC\ContentCaching;


class TestCache implements ContentCache {

    private $cachedItems = array();


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
    public function getCachedResult($url, $maxAgeInMinutes) {
        return isset($this->cachedItems[$url]) ? $this->cachedItems[$url][0] : null;
    }


    /**
     * Cache the result of a request URL for future use.
     *
     * @param string $url
     * @param int $maxAgeInMinutes
     * @param mixed $result
     *
     */
    public function cacheResult($url, $maxAgeInMinutes, $result) {
        $this->cachedItems[$url] = array($result, $maxAgeInMinutes);
    }

    /**
     * @return array
     */
    public function getCachedItems() {
        return $this->cachedItems;
    }


}
