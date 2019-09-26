<?php

namespace Kinikit\MVC\Routing;

use Kinikit\Core\Configuration\Configuration;
use Kinikit\Core\Configuration\FileResolver;
use Kinikit\Core\DependencyInjection\Container;
use Kinikit\Core\Reflection\ClassInspector;
use Kinikit\Core\Reflection\ClassInspectorProvider;
use Kinikit\Core\Reflection\Method;
use Kinikit\MVC\Request\Request;
use Kinikit\MVC\Request\URL;
use Kinikit\MVC\Response\JSONResponse;
use Kinikit\MVC\Response\Response;
use Kinikit\MVC\Response\ViewNotFoundException;
use Kinikit\MVC\RouteHandler\ControllerRouteHandler;
use Kinikit\MVC\RouteHandler\RouteHandler;
use Kinikit\MVC\RouteHandler\ViewOnlyRouteHandler;
use Kinikit\MVC\RouteHandler\DecoratorRouteHandler;
use Kinikit\MVC\RouteHandler\MissingDecoratorHandlerException;

/**
 * Resolve the current request route to a handler which is an instance of RouteHandler.
 * Throw a RouteNotFound exception if the route doesn't map to a handler.
 *
 * @noProxy
 * Class ControllerMethodResolver
 */
class RouteResolver {

    /**
     * @var ClassInspectorProvider
     */
    private $classInspectorProvider;

    /**
     * @var FileResolver
     */
    private $fileResolver;

    /**
     * Construct with necessary dependencies
     *
     *
     * ControllerMethodResolver constructor.
     *
     * @param ClassInspectorProvider $classInspectorProvider
     * @param FileResolver $fileResolver
     */
    public function __construct($classInspectorProvider, $fileResolver) {
        $this->classInspectorProvider = $classInspectorProvider;
        $this->fileResolver = $fileResolver;
    }


    /**
     * Main resolve method, uses the current request and maps to a RouteHandler if possible.
     *
     * @param Request $request
     * @param string $url
     * @param bool $allowDecoration
     *
     * @return RouteHandler
     * @throws RouteNotFoundException
     */
    public function resolve($request, $url = null, $allowDecoration = true) {

        if (!$url)
            $url = $request->getUrl();


        $routeHandler = null;

        // Check for any controllers matching direct partials of request path first.
        // This is prioritised because it is the most usual scenario for REST APIs etc.
        list($controllerClassInspector, $remainingSegments) = $this->resolveController("Controllers", $url->getPathSegments());
        if ($controllerClassInspector) {
            $method = $this->resolveMethod($request, $controllerClassInspector, $remainingSegments);
            if ($method) {
                $routeHandler = new ControllerRouteHandler($method, $request, join("/", $remainingSegments));
            }
        }


        // Now look for any direct views mathing the full request path.
        try {
            $routeHandler = new ViewOnlyRouteHandler($url->getPath(), $request);
        } catch (ViewNotFoundException $e) {
        }


        // If no route handler has been found, or if it is a view only route handler or if not a
        // JSON response method, check for decoration.
        if ($allowDecoration && (!$routeHandler || $routeHandler->getRouteType() == RouteHandler::ROUTE_TYPE_WEB)) {

            // Check for decorators in the path and failing that for a default decorator.
            list($decoratorClassInspector, $remainingSegments) = $this->resolveController("Decorators", $url->getPathSegments());
            if (!$decoratorClassInspector && $defaultDecorator = Configuration::readParameter("default.decorator")) {
                $decoratorSegs = explode("/", $defaultDecorator);
                list($decoratorClassInspector, $remainingSegments) = $this->resolveController("Decorators", $decoratorSegs);
            }

            // If we get a hit set the route handler.
            if ($decoratorClassInspector) {


                try {
                    $decoratorMethod = $decoratorClassInspector->getPublicMethod("handleRequest");
                } catch (\ReflectionException $e) {
                    throw new MissingDecoratorHandlerException(join("/", $url->getPathSegments()));
                }

                if (!$routeHandler) {

                    $routeHandler = $this->resolve($request, new URL(strtolower($url->getProtocol()) . "://" . $url->getHost() . "/" . join("/", $remainingSegments)), false);
                }

                if ($routeHandler)
                    $routeHandler = new DecoratorRouteHandler($decoratorMethod, $routeHandler, $request);
            }

        }


        if (!$routeHandler)
            throw new RouteNotFoundException($url->getPath());

        return $routeHandler;

    }


    /**
     * Resolve a controller within an initial directory using path segments
     *
     * @return [ClassInspector|mixed]
     */
    private function resolveController($initialDirectory, $pathSegments) {

        $currentPath = $initialDirectory;
        foreach ($pathSegments as $index => $segment) {
            $currentPath .= "/$segment";
            if ($resolved = $this->fileResolver->resolveFile($currentPath . ".php", true)) {
                $controllerSource = file_get_contents($resolved);
                preg_match("/namespace (.*?);/", $controllerSource, $namespaceMatches);
                preg_match("/class (.*?) {/", $controllerSource, $classMatches);
                if (sizeof($classMatches) > 1) {

                    $className = trim(($namespaceMatches[1] ?? "") . "\\" . $classMatches[1]);

                    if (!class_exists($className))
                        include_once $resolved;

                    return [$this->classInspectorProvider->getClassInspector($className),
                        array_slice($pathSegments, $index + 1)];
                }
            }
        }

        return null;
    }


    /**
     * Resolve a method for a controller using remaining path segments
     *
     * @param $request
     * @param ClassInspector $controllerClassInspector
     * @param string[] $pathSegments
     *
     * @return Method
     */
    private function resolveMethod($request, $controllerClassInspector, $pathSegments) {

        // Get a match string for matching below
        $requestPath = join("/", $pathSegments);

        // Obtain Request Method for matching below.
        $requestMethod = $request->getRequestMethod();

        // Loop through all public methods looking for viable matches
        $methodMatch = null;
        foreach ($controllerClassInspector->getPublicMethods() as $publicMethod) {

            // Derive the path to compare with our
            $methodPath = "";
            if (sizeof($publicMethod->getMethodAnnotations()["http"] ?? []) > 0) {
                $httpAnnotation = $publicMethod->getMethodAnnotations()["http"][0]->getValue();
                $splitAnnotation = explode(" ", trim($httpAnnotation));
                $methodMethod = trim($splitAnnotation[0]);

                // Continue if no method match for optimisation.
                if ($methodMethod != $requestMethod)
                    continue;

                if (sizeof($splitAnnotation) > 1) {
                    $methodPath = ltrim($splitAnnotation[1], "/");
                    $methodPath = str_replace("/", "\\/", $methodPath);
                    $methodPath = preg_replace("/\\$[0-9a-zA-Z_]*/", "[^\\/]*?", $methodPath);

                }
            } else {
                $methodPath = $publicMethod->getMethodName();
            }


            // If we have a match, record it.
            if (preg_match("/^$methodPath$/", $requestPath, $matches)) {
                $methodMatch = $publicMethod;
            }

        }

        if (!$methodMatch) {
            $methodMatch = $controllerClassInspector->getPublicMethod("handleRequest") ?? null;

        }

        return $methodMatch;


    }

}
