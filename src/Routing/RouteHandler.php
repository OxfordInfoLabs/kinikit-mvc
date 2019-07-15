<?php


namespace Kinikit\MVC\Routing;


use Kinikit\MVC\RateLimiter\RateLimitConfig;

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
     * @var string
     */
    protected $cacheTime = null;


    /**
     * Should construct with rate limiter config and cache time
     * to allow the framework to use these in conjunction with the execute method below.
     * Both can be null.
     *
     * RouteHandler constructor.
     * @param RateLimitConfig $rateLimiterConfig
     * @param string $cacheTime
     */
    public function __construct($rateLimiterConfig, $cacheTime) {
        $this->rateLimiterConfig = $rateLimiterConfig;
        $this->cacheTime = $cacheTime;
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
    public function getCacheTime() {
        return $this->cacheTime;
    }


    /**
     * Execute any route logic and stream the response straight to
     * stdout.  Typically route handlers should defer any heavy lifting
     * to this method as the framework will optimise for rate limiting
     * prior to calling this method.
     *
     * @return mixed
     */
    public abstract function executeAndSendResponse();


}
