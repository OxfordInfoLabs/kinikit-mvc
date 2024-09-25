<?php


namespace Kinikit\MVC\Routing;


use Kinikit\Core\Configuration\ConfigFile;
use Kinikit\Core\DependencyInjection\Container;
use Kinikit\Core\Reflection\ClassInspectorProvider;

/**
 *
 * @noProxy
 *
 * Class RouteInterceptorProcessor
 * @package Kinikit\MVC\Routing
 */
class RouteInterceptorProcessor {

    /**
     * @var string[string]
     */
    private $interceptorsByUrlPattern;


    /**
     * Used when constructing interceptor handlers.
     *
     * @var ClassInspectorProvider
     */
    private $classInspectorProvider;

    /**
     *
     * RouteInterceptorProcessor constructor.
     * @param ClassInspectorProvider $classInspectorProvider
     */
    public function __construct($classInspectorProvider) {
        $this->classInspectorProvider = $classInspectorProvider;
    }

    /**
     * Add an interceptor mapping for a specified request path pattern.
     *
     * @param $requestPathPattern
     * @param $interceptorClass
     */
    public function addInterceptor($requestPathPattern, $interceptorClass) {
        $this->getInterceptors();
        $this->interceptorsByUrlPattern[$requestPathPattern] = $interceptorClass;
    }


    /**
     * Get a Route interceptor Handler object for the passed request path.
     *
     * @param string $requestPath
     */
    public function getInterceptorHandlerForRequest($requestPath) {

        $interceptors = $this->getInterceptors();
        $interceptorInstances = [];
        foreach ($interceptors as $urlPattern => $interceptorClass) {
            $urlPattern = str_replace(["*", "/"], [".*?", "\\/"], ltrim($urlPattern, "/"));
            if (preg_match("/^" . $urlPattern . "$/", ltrim($requestPath, "/"))) {
                $interceptorInstances[] = Container::instance()->get($interceptorClass);
            }
        }

        return new RouteInterceptorHandler($interceptorInstances, $this->classInspectorProvider);
    }


    /**
     * Load from config file if required.
     */
    private function getInterceptors() {
        if (!$this->interceptorsByUrlPattern) {
            $configFile = new ConfigFile("Config/routeinterceptors.txt");
            $this->interceptorsByUrlPattern = $configFile->getAllParameters();
        }
        return $this->interceptorsByUrlPattern;
    }


}
