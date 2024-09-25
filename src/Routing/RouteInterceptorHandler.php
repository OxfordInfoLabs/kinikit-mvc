<?php


namespace Kinikit\MVC\Routing;


use Kinikit\Core\Reflection\ClassInspectorProvider;
use Kinikit\MVC\ContentCaching\ContentCacheConfig;
use Kinikit\MVC\RateLimiter\RateLimiterConfig;
use Kinikit\MVC\Request\Request;
use Kinikit\MVC\Response\Response;

class RouteInterceptorHandler {

    /**
     * Array of interceptors to process.
     *
     * @var RouteInterceptor[]
     */
    private $interceptors = [];

    /**
     * Rate limit config if any route interceptors have defined rate limiting.
     *
     * @var RateLimiterConfig
     */
    private $rateLimiterConfig = null;

    /**
     * Content cache config if any route interceptors have defined caching
     *
     * @var ContentCacheConfig
     */
    private $contentCacheConfig = null;


    /**
     * @var ClassInspectorProvider
     */
    private $classInspectorProvider;


    /**
     * Construct as value object with all members
     *
     * RouteInterceptorHandler constructor.
     *
     * @param RouteInterceptor[] $interceptors
     * @param Request $request
     * @param ClassInspectorProvider $classInspectorProvider
     *
     */
    public function __construct($interceptors, $classInspectorProvider) {
        $this->interceptors = $interceptors;
        $this->classInspectorProvider = $classInspectorProvider;
        $this->parseRateLimitAndCache();
    }

    /**
     * Get the rate limiter config
     *
     * @return RateLimiterConfig
     */
    public function getRateLimiterConfig() {
        return $this->rateLimiterConfig;
    }

    /**
     * Get the content cache config if defined.
     *
     * @return ContentCacheConfig
     */
    public function getContentCacheConfig() {
        return $this->contentCacheConfig;
    }


    /**
     * Process before route interceptors.  If a response is received
     * for any interceptors this will be immediately returned.
     *
     * @param Request $request
     * @return Response|null
     *
     */
    public function processBeforeRoute($request) {
        foreach ($this->interceptors as $interceptor) {
            $response = $interceptor->beforeRoute($request);
            if ($response) {
                return $response;
            }
        }
    }


    /**
     * Process before route interceptors.  If a response is received
     * for any interceptors this will be immediately returned.
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function processAfterRoute($request, $response) {

         // Call all interceptors, augment response at each stage.
        foreach ($this->interceptors as $interceptor) {
            $response = $interceptor->afterRoute($request, $response);
        }

        return $response;
    }


    // Parse the rate limit and cache values if set from annotations
    private function parseRateLimitAndCache() {

        $rateLimited = false;
        $rateLimit = null;
        $rateLimitMultiplier = null;
        $cached = false;
        $cacheTime = null;

        foreach ($this->interceptors as $interceptor) {
            $inspector = $this->classInspectorProvider->getClassInspector(get_class($interceptor));
            $annotations = $inspector->getClassAnnotations();

            // Derive rate limit config
            $rateLimited = $rateLimited || isset($annotations["rateLimited"]);
            if (!$rateLimit) $rateLimit = isset($annotations["rateLimit"][0]) ? $annotations["rateLimit"][0]->getValue() : null;
            if (!$rateLimitMultiplier) $rateLimitMultiplier = isset($annotations["rateLimitMultiplier"][0]) ? $annotations["rateLimitMultiplier"][0]->getValue() : null;

            // Derive cache config
            $cached = $cached || isset($annotations["cached"]);
            if (!$cacheTime) $cacheTime = isset($annotations["cacheTime"][0]) ? $annotations["cacheTime"][0]->getValue() : null;

        }

        // Only set the cache and ratelimit configs if any exist.
        if ($cached || $cacheTime) $this->contentCacheConfig = new ContentCacheConfig($cacheTime);
        if ($rateLimited || $rateLimit || $rateLimitMultiplier) $this->rateLimiterConfig = new RateLimiterConfig($rateLimit, $rateLimitMultiplier);
    }


}
