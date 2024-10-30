<?php

namespace Kinikit\MVC\Routing;

use Kinikit\Core\Reflection\ClassInspectorProvider;
use Kinikit\MVC\Request\Headers;
use Kinikit\MVC\Request\Request;

include_once "autoloader.php";

class RouteInterceptorProcessorTest extends \PHPUnit\Framework\TestCase {


    public function testCanGetMatchingHandlerUsingConfiguredRouteInterceptors() {

        $classInspectorProvider = new ClassInspectorProvider();
        $routeInterceptorProcessor = new RouteInterceptorProcessor($classInspectorProvider);


        $handler = $routeInterceptorProcessor->getInterceptorHandlerForRequest("/testpath");
        $this->assertEquals(new RouteInterceptorHandler([new TestRouteInterceptor1()], $classInspectorProvider), $handler);

     
        $handler = $routeInterceptorProcessor->getInterceptorHandlerForRequest("/zone");
        $this->assertEquals(new RouteInterceptorHandler([new TestRouteInterceptor1()], $classInspectorProvider), $handler);

        $handler = $routeInterceptorProcessor->getInterceptorHandlerForRequest("/zone/test");
        $this->assertEquals(new RouteInterceptorHandler([new TestRouteInterceptor1(), new TestRouteInterceptor2()], $classInspectorProvider), $handler);


        $handler = $routeInterceptorProcessor->getInterceptorHandlerForRequest("/sub/test");
        $this->assertEquals(new RouteInterceptorHandler([new TestRouteInterceptor1()], $classInspectorProvider), $handler);


        $handler = $routeInterceptorProcessor->getInterceptorHandlerForRequest("/sub/nestedsimple");
        $this->assertEquals(new RouteInterceptorHandler([new TestRouteInterceptor1(), new TestRouteInterceptor3()], $classInspectorProvider), $handler);

    }

}
