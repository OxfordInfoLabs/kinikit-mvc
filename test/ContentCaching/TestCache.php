<?php


namespace Kinikit\MVC\ContentCaching;


use Kinikit\Core\Caching\CacheProvider;

class TestCache implements CacheProvider {

    private array $cachedItems = [];


    /**
     * Get the cached result of a request URL.
     *
     * Return a value if a cached value is to be returned or null if we need to revalidate.
     *
     * @param string $url
     *
     * @return mixed
     */
    public function getCachedResult(string $url) {
        return $this->cachedItems[$url][0] ?? null;
    }


    /**
     * Cache the result of a request URL for future use.
     *
     * @param string $url
     * @param int $maxAgeInMinutes
     * @param mixed $result
     * @return void
     */
    public function cacheResult(string $url, int $maxAgeInMinutes, mixed $result): void {
        $this->cachedItems[$url] =[$result, $maxAgeInMinutes];
    }

    /**
     * @return array
     */
    public function getCachedItems(): array {
        return $this->cachedItems;
    }


    public function set(string $key, mixed $value, int $ttl): void {
        $this->cacheResult($key, $ttl, $value);
    }

    public function get(string $key, ?string $returnClass = null) {
        return $this->getCachedResult($key);
    }

    public function lookup(string $key, callable $generatorFunction, int $ttl, array $params = [], ?string $returnClass = null) {
        // TODO: Implement lookup() method.
    }

    public function clearCache(): void {
        // TODO: Implement clearCache() method.
    }
}
