<?php

namespace Kinikit\MVC\Routing;

use Kinikit\Core\Configuration\FileResolver;
use Kinikit\Core\DependencyInjection\Container;
use Kinikit\Core\Reflection\ClassInspector;
use Kinikit\Core\Reflection\ClassInspectorProvider;
use Kinikit\Core\Reflection\Method;
use Kinikit\MVC\Request\Request;
use Kinikit\MVC\Response\ViewNotFoundException;

/**
 * Resolve the current request route to a handler which is an instance of RouteHandler.
 * Throw a RouteNotFound exception if the route doesn't map to a handler.
 *
 * Class ControllerMethodResolver
 */
class RouteResolver {

    /**
     * @var Request
     */
    private $request;

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
     * @param Request $request
     * @param ClassInspectorProvider $classInspectorProvider
     * @param FileResolver $fileResolver
     */
    public function __construct($request, $classInspectorProvider, $fileResolver) {
        $this->request = $request;
        $this->classInspectorProvider = $classInspectorProvider;
        $this->fileResolver = $fileResolver;
    }


    /**
     * Main resolve method, uses the current request and maps to a RouteHandler if possible.
     *
     *
     * @return RouteHandler
     * @throws RouteNotFoundException
     */
    public function resolve($url = null) {

        if (!$url)
            $url = $this->request->getUrl();


        // Check for any controllers matching partials of request path
        list($controllerClassInspector, $remainingSegments) = $this->resolveController("Controllers", $url->getPathSegments());
        if ($controllerClassInspector) {
            $method = $this->resolveMethod($controllerClassInspector, $remainingSegments);
            if ($method) {
                return new ControllerRouteHandler($method, $this->request);
            }

        }

        // Finally, check for view only routes.
        $requestPath = $url->getPath();
        try {
            return new ViewOnlyRouteHandler($requestPath);
        } catch (ViewNotFoundException $e) {
            throw new RouteNotFoundException($requestPath);
        }

    }


    /**
     * Resolve a controller within an initial directory using path segments
     *
     * @return [mixed]
     */
    private function resolveController($initialDirectory, $pathSegments) {

        $currentPath = $initialDirectory;
        foreach ($pathSegments as $index => $segment) {
            $currentPath .= "/$segment";
            if ($resolved = $this->fileResolver->resolveFile($currentPath . ".php")) {
                $controllerSource = file_get_contents($resolved);
                preg_match("/namespace (.*?);/", $controllerSource, $namespaceMatches);
                preg_match("/class (.*?) {/", $controllerSource, $classMatches);
                if (sizeof($classMatches) > 1) {

                    $className = ($namespaceMatches[1] ?? "") . "\\" . $classMatches[1];

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
     * @param ClassInspector $controllerClassInspector
     * @param string[] $pathSegments
     *
     * @return Method
     */
    private function resolveMethod($controllerClassInspector, $pathSegments) {

        // Get a match string for matching below
        $requestPath = join("/", $pathSegments);

        // Obtain Request Method for matching below.
        $requestMethod = $this->request->getRequestMethod();

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
