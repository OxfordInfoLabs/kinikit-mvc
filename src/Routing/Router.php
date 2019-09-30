<?php


namespace Kinikit\MVC\Routing;

use Kinikit\Core\Configuration\Configuration;
use Kinikit\Core\Configuration\FileResolver;
use Kinikit\Core\DependencyInjection\Container;
use Kinikit\Core\Exception\StatusException;
use Kinikit\Core\Init;
use Kinikit\MVC\Alias\AliasMapper;
use Kinikit\MVC\ContentCaching\ContentCacheEvaluator;
use Kinikit\MVC\ContentCaching\ContentCacheConfig;
use Kinikit\MVC\RateLimiter\RateLimiterConfig;
use Kinikit\MVC\RateLimiter\RateLimiterEvaluator;
use Kinikit\MVC\Request\Headers;
use Kinikit\MVC\Request\Request;
use Kinikit\MVC\Request\URL;
use Kinikit\MVC\Response\JSONResponse;
use Kinikit\MVC\Response\Redirect;
use Kinikit\MVC\Response\Response;
use Kinikit\MVC\Response\SimpleResponse;
use Kinikit\MVC\RouteHandler\RouteHandler;

/**
 * Main entry point for MVC applications
 *
 * @noProxy
 *
 * Class Router
 * @package Kinikit\MVC\Routing
 */
class Router {

    /**
     * @var Request
     */
    private $request;

    /**
     * @var AliasMapper
     */
    private $aliasMapper;

    /**
     * @var RouteResolver
     */
    private $routeResolver;


    /**
     * @var RouteInterceptorProcessor
     */
    private $routeInterceptorProcessor;


    /**
     * @var FileResolver
     */
    private $fileResolver;


    /**
     * Router constructor.
     *
     * @param Request $request
     * @param AliasMapper $aliasMapper
     * @param RouteResolver $routeResolver
     * @param RouteInterceptorProcessor $routeInterceptorProcessor
     * @param FileResolver $fileResolver
     */
    public function __construct($request, $aliasMapper, $routeResolver, $routeInterceptorProcessor, $fileResolver) {
        $this->request = $request;
        $this->aliasMapper = $aliasMapper;
        $this->routeResolver = $routeResolver;
        $this->routeInterceptorProcessor = $routeInterceptorProcessor;
        $this->fileResolver = $fileResolver;
    }

    /**
     * Main static entry point to the MVC application for convenience of instantiation
     */
    public static function route() {

        // New initialiser
        Container::instance()->get(Init::class);

        /**
         * @var Router $router
         */
        $router = Container::instance()->get(Router::class);

        $response = $router->processRequest($router->request);
        if ($response) {
            // Send the response, sending headers only if this was a HEAD request.
            $response->send($router->request->getRequestMethod() == Request::METHOD_HEAD);
        }
    }

    /**
     * Process the passed request and return a response
     *
     * @param Request $request
     * @return Response
     */
    public function processRequest($request) {

        // Append our path to the file resolver
        $this->fileResolver->addSearchPath(__DIR__ . "/..");

        // Initialise response
        $response = null;

        try {

            // Firstly apply any aliases to the mix
            $url = $request->getUrl();

            $aliasMapped = $this->aliasMapper->mapURL("/" . $url->getPath(true));

            // If we get a response, return it immediately as it represents a redirect.
            if ($aliasMapped instanceof Redirect) {
                return $aliasMapped;
            } else {
                $url = new URL(strtolower($url->getProtocol()) . "://" . $url->getHost() . ":" . $url->getPort() . "/" . $aliasMapped);
            }

            // Resolve the route up front to help us with exception handling later
            $routeHandler = $this->routeResolver->resolve($request, $url);

            // Get the interceptor handler for this request.
            $routeInterceptorHandler = $this->routeInterceptorProcessor->getInterceptorHandlerForRequest($url->getPath());

            /**
             * Grab any rate limiter config and / or cache config.
             *
             * @var RateLimiterConfig $rateLimiterConfig
             * @var ContentCacheConfig $cacheConfig
             */
            list($rateLimiterConfig, $cacheConfig) = $this->getRateLimitAndCachingConfig($routeHandler, $routeInterceptorHandler);

            // If rate limiter config, apply rate limiting now
            if ($rateLimiterConfig) {
                $rateLimiterEvaluator = Container::instance()->get(RateLimiterEvaluator::class);
                $rateLimiterEvaluator->evaluateRateLimiter($rateLimiterConfig);
            }


            // If cache config, checked for cached response first.
            if ($cacheConfig) {
                $cacheEvaluator = Container::instance()->get(ContentCacheEvaluator::class);
                $response = $cacheEvaluator->getCachedResult($cacheConfig, $request->getUrl()->getPath(true));
                if ($response) {
                    return $response;
                }
            }


            // Run before interceptors.
            $response = $routeInterceptorHandler->processBeforeRoute($request);

            // Handle the route and collect the response only if no response from before route
            if (!$response)
                $response = $routeHandler->handleRoute();


        } catch (\Throwable $e) {

            // Generate responses based upon the route type, falling back to web for exceptions raised at the first stage.
            $routeType = isset($routeHandler) ? $routeHandler->getRouteType() : RouteHandler::ROUTE_TYPE_WEB;

            // Get the status code
            $responseCode = $e instanceof StatusException ? $e->getStatusCode() : 500;

            // Handle web route errors.
            if ($routeType == RouteHandler::ROUTE_TYPE_WEB) {

                // Configure route to specific one if a status exception or to the general one otherwise.
                $route = $e instanceof StatusException ? "error/error" . $responseCode : "error/error";

                try {
                    $response = $this->callErrorRoute($route, $e->getMessage(), $e->getCode(), $responseCode);
                } catch (RouteNotFoundException $e) {
                    if ($route != "error/error") {
                        try {
                            $response = $this->callErrorRoute("error/error", $e->getMessage(), $e->getCode(), $responseCode);
                        } catch (RouteNotFoundException $e) {
                            $response = new SimpleResponse("Missing Error Route Found at error/error", Response::RESPONSE_GENERAL_ERROR);
                        }
                    } else {
                        $response = new SimpleResponse("Missing Error Route Found at error/error", Response::RESPONSE_GENERAL_ERROR);
                    }
                }

            } // Handle JSON route errors.
            else {
                $response = new JSONResponse(["errorMessage" => $e->getMessage(), "errorCode" => $e->getCode()], $responseCode);
            }


        }

        // Run after routes if possible
        if (isset($routeInterceptorHandler)) {
            $response = $routeInterceptorHandler->processAfterRoute($response);
        }

        // Add to cache if required.
        if (isset($cacheConfig) && $cacheConfig) {
            $cacheEvaluator->cacheResult($cacheConfig, $request->getUrl()->getPath(true), $response);
        }

        // Handle Cross Origin logic before return.
        $accessControlOrigin = Configuration::readParameter("access.control.origin");
        if (!$accessControlOrigin) {
            $accessControlOrigin = "*";
        } else if ($accessControlOrigin == "REFERRER") {
            $referrer = $request->getReferringURL();
            if ($referrer) {
                $accessControlOrigin = strtolower($referrer->getProtocol()) . "://" . $referrer->getHost() . ($referrer->getPort() ? ":" . $referrer->getPort() : "");
                $response->setHeader(\Kinikit\MVC\Response\Headers::HEADER_ACCESS_CONTROL_ALLOW_CREDENTIALS, "true");
            }
        }
        $response->setHeader(\Kinikit\MVC\Response\Headers::HEADER_ACCESS_CONTROL_ALLOW_ORIGIN, $accessControlOrigin);


        // Finally return the response.
        return $response;
    }


    /**
     * @param RouteHandler $routeHandler
     * @param RouteInterceptorHandler $routeInterceptorHandler
     */
    private function getRateLimitAndCachingConfig($routeHandler, $routeInterceptorHandler) {
        $rateLimited = $cached = false;
        $rateLimit = $rateLimitMultiplier = $cacheTime = null;

        // Handle rate limiting, prioritising the route over the interceptor.
        if ($routeConfig = $routeHandler->getRateLimiterConfig()) {
            $rateLimited = true;
            $rateLimit = $routeConfig->getRateLimit();
            $rateLimitMultiplier = $routeConfig->getRateLimit() ? null : $routeConfig->getRateLimitMultiplier();
        }
        if ($interceptorConfig = $routeInterceptorHandler->getRateLimiterConfig()) {
            $rateLimited = true;
            if (!$rateLimit && !$rateLimitMultiplier) {
                $rateLimit = $interceptorConfig->getRateLimit();
                $rateLimitMultiplier = $interceptorConfig->getRateLimit() ? null : $interceptorConfig->getRateLimitMultiplier();
            }
        }

        // Handle caching, prioritising the route over the interceptor
        if ($routeConfig = $routeHandler->getContentCacheConfig()) {
            $cached = true;
            $cacheTime = $routeConfig->getCacheTime();
        }
        if ($interceptorConfig = $routeInterceptorHandler->getContentCacheConfig()) {
            $cached = true;
            if (!$cacheTime) $cacheTime = $interceptorConfig->getCacheTime();
        }

        return [$rateLimited ? new RateLimiterConfig($rateLimit, $rateLimitMultiplier) : null,
            $cached ? new ContentCacheConfig($cacheTime) : null];

    }


    // Call the error route.
    private function callErrorRoute($route, $errorMessage, $errorCode, $responseCode) {

        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = $route;
        $_GET = ["errorMessage" => $errorMessage, "errorCode" => $errorCode];


        $routeHandler = $this->routeResolver->resolve(new Request(new Headers()));
        $response = $routeHandler->handleRoute();

        if (!$response->getResponseCode() || $response->getResponseCode() == 200) {
            $response->setResponseCode($responseCode);
        }

        return $response;
    }


}
