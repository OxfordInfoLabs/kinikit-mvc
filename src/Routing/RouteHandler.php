<?php


namespace Kinikit\MVC\Routing;


use Kinikit\MVC\Caching\CacheConfig;
use Kinikit\MVC\RateLimiter\RateLimitConfig;
use Kinikit\MVC\Response\Response;

abstract class RouteHandler {

    /**
     * Rate limit config if the target of this route handler has configured rate limiting.
     *
     * @var RateLimitConfig
     */
    protected $rateLimiterConfig = null;

    /**
     * Cache time as a string e.g. 3m, 1y etc if this route handler has configured caching.
     *
     * @var CacheConfig
     */
    protected $cacheConfig = null;


    /**
     * Should construct with rate limiter config and cache time
     * to allow the framework to use these in conjunction with the execute method below.
     * Both can be null.
     *
     * RouteHandler constructor.
     * @param RateLimitConfig $rateLimiterConfig
     * @param CacheConfig $cacheConfig
     */
    public function __construct($rateLimiterConfig, $cacheConfig) {
        $this->rateLimiterConfig = $rateLimiterConfig;
        $this->cacheConfig = $cacheConfig;
    }


    /**
     * @return RateLimitConfig
     */
    public function getRateLimiterConfig() {
        return $this->rateLimiterConfig;
    }

    /**
     * @return string
     */
    public function getCacheConfig() {
        return $this->cacheConfig;
    }


    /**
     * Handle the route including any exception handling and return a response object.
     *
     * @return Response
     */
    public abstract function handleRoute();


}
