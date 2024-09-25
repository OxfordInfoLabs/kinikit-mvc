<?php


namespace Kinikit\MVC\Routing;

use Kinikit\Core\Binding\ObjectBinder;
use Kinikit\Core\Bootstrapper;
use Kinikit\Core\Configuration\FileResolver;
use Kinikit\Core\DependencyInjection\Container;
use Kinikit\Core\Exception\StatusException;
use Kinikit\Core\Logging\Logger;
use Kinikit\MVC\Alias\AliasMapper;
use Kinikit\MVC\ContentCaching\ContentCacheConfig;
use Kinikit\MVC\ContentCaching\ContentCacheEvaluator;
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
     * @var ObjectBinder
     */
    private $objectBinder;


    /**
     * Router constructor.
     *
     * @param Request $request
     * @param AliasMapper $aliasMapper
     * @param RouteResolver $routeResolver
     * @param RouteInterceptorProcessor $routeInterceptorProcessor
     * @param FileResolver $fileResolver
     * @param ObjectBinder $objectBinder
     */
    public function __construct($request, $aliasMapper, $routeResolver, $routeInterceptorProcessor, $fileResolver, $objectBinder) {
        $this->request = $request;
        $this->aliasMapper = $aliasMapper;
        $this->routeResolver = $routeResolver;
        $this->routeInterceptorProcessor = $routeInterceptorProcessor;
        $this->fileResolver = $fileResolver;
        $this->objectBinder = $objectBinder;
    }

    /**
     * Main static entry point to the MVC application for convenience of instantiation
     */
    public static function route() {

        // New initialiser
        Container::instance()->get(Bootstrapper::class);

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


            // Get the interceptor handler for this request.
            $routeInterceptorHandler = $this->routeInterceptorProcessor->getInterceptorHandlerForRequest($url->getPath());



            // Run before interceptors as first stage.
            $response = $routeInterceptorHandler->processBeforeRoute($request);

            // If no response from before route, proceed to resolve the route.
            if (!$response) {

                // Resolve the route up front to help us with exception handling later
                $routeHandler = $this->routeResolver->resolve($request, $url);

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


                // Handle the route and collect the response only if no response from before route

                $response = $routeHandler->handleRoute();
            }


        } catch (\Throwable $e) {


            // Generate responses based upon the route type, falling back to json for exceptions raised at the first stage.
            $routeType = isset($routeHandler) ? $routeHandler->getRouteType() : RouteHandler::ROUTE_TYPE_JSON;


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

                $exceptionArray = $this->objectBinder->bindToArray($e);

                Logger::log($e);

                if (is_array($exceptionArray)) {
                    unset($exceptionArray["file"]);
                    unset($exceptionArray["line"]);
                    unset($exceptionArray["previous"]);
                    unset($exceptionArray["trace"]);
                    unset($exceptionArray["traceAsString"]);
                }

                $response = new JSONResponse($exceptionArray, $responseCode);
            }


        }

        // Run after routes if possible
        if (isset($routeInterceptorHandler)) {
            $response = $routeInterceptorHandler->processAfterRoute($request, $response);
        }

        // Add to cache if required.
        if (isset($cacheConfig) && $cacheConfig) {
            $cacheEvaluator->cacheResult($cacheConfig, $request->getUrl()->getPath(true), $response);
        }

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
