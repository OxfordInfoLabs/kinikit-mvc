<?php

namespace Kinikit\MVC\Routing;

use Kinikit\Core\DependencyInjection\Container;
use Kinikit\Core\Reflection\ClassInspectorProvider;
use Kinikit\MVC\ContentCaching\ContentCacheConfig;
use Kinikit\MVC\RateLimiter\RateLimiterConfig;
use Kinikit\MVC\Request\Headers;
use Kinikit\MVC\Request\Request;
use Kinikit\MVC\Response\SimpleResponse;

include_once "autoloader.php";

class RouteInterceptorHandlerTest extends \PHPUnit\Framework\TestCase {


    private $request;
    private $classInspectorProvider;


    public function setUp(): void {
        $this->request = Container::instance()->get(Request::class);
        $this->classInspectorProvider = Container::instance()->get(ClassInspectorProvider::class);
    }

    public function testRateLimiterAndCachingConfigurationsAreCorrectlyParsedFromInterceptors() {

        $handler = new RouteInterceptorHandler([new TestRouteInterceptor1()], $this->classInspectorProvider);
        $this->assertEquals(new RateLimiterConfig(null), $handler->getRateLimiterConfig());
        $this->assertNull($handler->getContentCacheConfig());

        $handler = new RouteInterceptorHandler([new TestRouteInterceptor2()], $this->classInspectorProvider);
        $this->assertNull($handler->getRateLimiterConfig());
        $this->assertEquals(new ContentCacheConfig("3d"), $handler->getContentCacheConfig());

        $handler = new RouteInterceptorHandler([new TestRouteInterceptor3()], $this->classInspectorProvider);
        $this->assertEquals(new RateLimiterConfig(5), $handler->getRateLimiterConfig());
        $this->assertEquals(new ContentCacheConfig(null), $handler->getContentCacheConfig());

        // Now do some doubles
        $handler = new RouteInterceptorHandler([new TestRouteInterceptor1(), new TestRouteInterceptor2()], $this->classInspectorProvider);
        $this->assertEquals(new RateLimiterConfig(null), $handler->getRateLimiterConfig());
        $this->assertEquals(new ContentCacheConfig("3d"), $handler->getContentCacheConfig());


        $handler = new RouteInterceptorHandler([new TestRouteInterceptor1(), new TestRouteInterceptor3()], $this->classInspectorProvider);
        $this->assertEquals(new RateLimiterConfig(5), $handler->getRateLimiterConfig());
        $this->assertEquals(new ContentCacheConfig(null), $handler->getContentCacheConfig());


        // All 3
        $handler = new RouteInterceptorHandler([new TestRouteInterceptor1(), new TestRouteInterceptor2(), new TestRouteInterceptor3()], $this->classInspectorProvider);
        $this->assertEquals(new RateLimiterConfig(5), $handler->getRateLimiterConfig());
        $this->assertEquals(new ContentCacheConfig("3d"), $handler->getContentCacheConfig());

    }


    public function testAllBeforeRouteMethodsCalledOnProcess() {

        $handler = new RouteInterceptorHandler([new TestRouteInterceptor1()], $this->classInspectorProvider);
        $response = $handler->processBeforeRoute($this->request);
        $this->assertNull($response);

        $this->assertEquals(1, TestRouteInterceptor1::$beforeRoutes);
        $this->assertEquals(0, TestRouteInterceptor2::$beforeRoutes);
        $this->assertEquals(0, TestRouteInterceptor3::$beforeRoutes);

        $handler = new RouteInterceptorHandler([new TestRouteInterceptor2()], $this->classInspectorProvider);
        $response = $handler->processBeforeRoute($this->request);
        $this->assertNull($response);

        $this->assertEquals(1, TestRouteInterceptor1::$beforeRoutes);
        $this->assertEquals(1, TestRouteInterceptor2::$beforeRoutes);
        $this->assertEquals(0, TestRouteInterceptor3::$beforeRoutes);


        $handler = new RouteInterceptorHandler([new TestRouteInterceptor1(), new TestRouteInterceptor3()], $this->classInspectorProvider);
        $response = $handler->processBeforeRoute($this->request);
        $this->assertNull($response);

        $this->assertEquals(2, TestRouteInterceptor1::$beforeRoutes);
        $this->assertEquals(1, TestRouteInterceptor2::$beforeRoutes);
        $this->assertEquals(1, TestRouteInterceptor3::$beforeRoutes);

    }


    public function testAllAfterRouteMethodsCalledOnProcess() {

        $handler = new RouteInterceptorHandler([new TestRouteInterceptor1()], $this->classInspectorProvider);
        $response = $handler->processAfterRoute(new Request(new Headers()), new SimpleResponse("Hello world"));
        $this->assertEquals(new SimpleResponse("Hello world"), $response);

        $this->assertEquals(1, TestRouteInterceptor1::$afterRoutes);
        $this->assertEquals(0, TestRouteInterceptor2::$afterRoutes);
        $this->assertEquals(0, TestRouteInterceptor3::$afterRoutes);

        $handler = new RouteInterceptorHandler([new TestRouteInterceptor2()], $this->classInspectorProvider);
        $response = $handler->processAfterRoute(new Request(new Headers()), new SimpleResponse("Hello world"));
        $this->assertEquals(new SimpleResponse("Hello world"), $response);


        $this->assertEquals(1, TestRouteInterceptor1::$afterRoutes);
        $this->assertEquals(1, TestRouteInterceptor2::$afterRoutes);
        $this->assertEquals(0, TestRouteInterceptor3::$afterRoutes);


        $handler = new RouteInterceptorHandler([new TestRouteInterceptor1(), new TestRouteInterceptor3()], $this->classInspectorProvider);
        $response = $handler->processAfterRoute(new Request(new Headers()), new SimpleResponse("Hello world"));
        $this->assertEquals(new SimpleResponse("Hello world"), $response);


        $this->assertEquals(2, TestRouteInterceptor1::$afterRoutes);
        $this->assertEquals(1, TestRouteInterceptor2::$afterRoutes);
        $this->assertEquals(1, TestRouteInterceptor3::$afterRoutes);

    }


    public function testResponseReturnedFromBeforeMethodIsReturnedDirectlyAndNoOtherInterceptorsCalled() {

        TestRouteInterceptor1::$beforeRoutes = 0;
        TestRouteInterceptor2::$beforeRoutes = 0;
        TestRouteInterceptor3::$beforeRoutes = 0;

        TestRouteInterceptor1::$returnResponseBefore = true;

        $handler = new RouteInterceptorHandler([new TestRouteInterceptor1(), new TestRouteInterceptor2(), new TestRouteInterceptor3()], $this->classInspectorProvider);
        $response = $handler->processBeforeRoute($this->request);
        $this->assertEquals(new SimpleResponse("RESPONSE"), $response);

        // Check that before stopped after first one because response sent.
        $this->assertEquals(1, TestRouteInterceptor1::$beforeRoutes);
        $this->assertEquals(0, TestRouteInterceptor2::$beforeRoutes);
        $this->assertEquals(0, TestRouteInterceptor3::$beforeRoutes);

        TestRouteInterceptor1::$returnResponseBefore = false;
    }

}
