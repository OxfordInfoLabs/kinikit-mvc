<?php


namespace Kinikit\MVC\RouteHandler;


use Kinikit\MVC\ContentCaching\ContentCacheConfig;
use Kinikit\MVC\RateLimiter\RateLimiterConfig;
use Kinikit\MVC\Response\Response;

abstract class RouteHandler {

    /**
     * Rate limit config if the target of this route handler has configured rate limiting.
     *
     * @var RateLimiterConfig
     */
    protected $rateLimiterConfig = null;

    /**
     * Content cache config if the target of this route handler has configured caching.
     *
     * @var ContentCacheConfig
     */
    protected $contentCacheConfig = null;

    /**
     * The route type for this route handler.  This is one of the constants listed below
     * and is used in the case of unexpected top level errors to determine whether or not to
     * return a JSON or Web response to the error.
     *
     * @var string
     */
    protected $routeType;


    // Route type constants.
    const ROUTE_TYPE_WEB = "WEB_ROUTE";
    const ROUTE_TYPE_JSON = "JSON_ROUTE";


    /**
     * Should construct with rate limiter config and cache time
     * to allow the framework to use these in conjunction with the execute method below.
     * Both can be null.
     *
     * RouteHandler constructor.
     * @param RateLimiterConfig $rateLimiterConfig
     * @param ContentCacheConfig $contentCacheConfig
     * @param $routeType
     */
    public function __construct($rateLimiterConfig, $contentCacheConfig, $routeType) {
        $this->rateLimiterConfig = $rateLimiterConfig;
        $this->contentCacheConfig = $contentCacheConfig;
        $this->routeType = $routeType;
    }


    /**
     * @return RateLimiterConfig
     */
    public function getRateLimiterConfig() {
        return $this->rateLimiterConfig;
    }

    /**
     * @return ContentCacheConfig
     */
    public function getContentCacheConfig() {
        return $this->contentCacheConfig;
    }

    /**
     * @return string
     */
    public function getRouteType() {
        return $this->routeType;
    }


    /**
     * Handle the route including any exception handling and return a response object.
     *
     * @return Response
     */
    public abstract function handleRoute();


}
