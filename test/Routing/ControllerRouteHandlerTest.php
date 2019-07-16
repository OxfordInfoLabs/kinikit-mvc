<?php


namespace Kinikit\MVC\Routing;


use http\Env\Response;
use Kinikit\Core\DependencyInjection\Container;
use Kinikit\Core\Reflection\ClassInspectorProvider;
use Kinikit\MVC\Controllers\REST;
use Kinikit\MVC\Objects\TestRESTObject;
use Kinikit\MVC\RateLimiter\RateLimitConfig;
use Kinikit\MVC\Request\Headers;
use Kinikit\MVC\Request\MockPHPInputStream;
use Kinikit\MVC\Request\Request;
use Kinikit\MVC\Response\JSONResponse;
use Kinikit\MVC\Response\View;
use Kinikit\MVC\Response\WebErrorResponse;

include_once "autoloader.php";

class ControllerRouteHandlerTest extends \PHPUnit\Framework\TestCase {

    /**
     * @var ClassInspectorProvider
     */
    private $classInspectorProvider;


    public function setUp(): void {
        $this->classInspectorProvider = Container::instance()->get(ClassInspectorProvider::class);
    }


    public function testCanHandleRouteForSimpleRESTGetMethodWithNoParams() {

        $method = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("list");

        $request = new Request(new Headers());

        $handler = new ControllerRouteHandler($method, $request, "");

        $list = array();
        for ($i = 0; $i < 10; $i++) {
            $list[] = new TestRESTObject("TEST " . $i, "test$i@test.com");
        }

        $this->assertEquals(new JSONResponse($list), $handler->handleRoute());


    }

    public function testCanHandleRouteForSimpleRESTGetMethodWithPathBasedParams() {

        $method = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("get");

        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "25");

        $this->assertEquals(new JSONResponse(new TestRESTObject("TEST 25", "test25@test.com", "GET SINGLE")), $handler->handleRoute());


        // Handle boolean input params properly as well
        $method = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("isTrue");

        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "true/true");

        $this->assertEquals(new JSONResponse(true), $handler->handleRoute());

    }


    public function testCanHandleRouteForSimpleGetOnlyRESTMethod() {

        // Handle boolean input params properly as well
        $method = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("getOnly");

        $_GET["param1"] = "Mark Polo";
        $_GET["param2"] = "23.56";
        $_GET["param3"] = "false";

        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "getOnly");

        $this->assertEquals(new JSONResponse(["Mark Polo", 23.56, false]), $handler->handleRoute());


    }


    public function testCanHandleRoutesForPayloadRESTMethods() {


        stream_wrapper_unregister("php");
        stream_wrapper_register("php", "Kinikit\MVC\Request\MockPHPInputStream");
        file_put_contents("php://input", '{"id": "23", "name": "Peter Pan", "email": "pan@neverland.com", "lastStatus": "SUCCESS"}');

        $method = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("create");

        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "");

        $this->assertEquals(new JSONResponse(new TestRESTObject("Peter Pan", "pan@neverland.com", "POSTED", 23)), $handler->handleRoute());


    }


    public function testCanHandleRouteForViewControllerAndResponseReturnedIntactWithAugmentedRequestParam() {

        include_once "Controllers/Zone/Simple.php";

        $method = $this->classInspectorProvider->getClassInspector(\Simple::class)->getPublicMethod("get");

        $_GET["title"] = "HELLO WORLD";

        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "");

        $this->assertEquals(new View("Simple", ["title" => "HELLO WORLD", "request" => $request]), $handler->handleRoute());

    }


    public function testExceptionsForJSONMethodsAreEncodedAsJSONResponsesWithAppropriateStatusCode() {

        $method = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("throwsException");

        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "");

        $this->assertEquals(new JSONResponse(["errorMessage" => "Bad REST Call", "errorCode" => 22], 500), $handler->handleRoute());


        $method = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("throwsStatusException");

        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "");

        $this->assertEquals(new JSONResponse(["errorMessage" => "Should return a custom error response code", "errorCode" => 50], 406), $handler->handleRoute());


    }


    public function testExceptionsForWebMethodsAreReturnedAsErrorResponses() {

        $method = $this->classInspectorProvider->getClassInspector(\Simple::class)->getPublicMethod("throwsError");

        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "");

        $this->assertEquals(new WebErrorResponse("Bad Web Request", 22, 500), $handler->handleRoute());


        $method = $this->classInspectorProvider->getClassInspector(\Simple::class)->getPublicMethod("throwsStatusError");

        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "");

        $this->assertEquals(new WebErrorResponse("Bad Custom Web Request", 22, 402), $handler->handleRoute());

    }


    public function testRequestObjectsAreAutowiredIfSuppliedToControllerMethods() {

        $_GET = [];
        stream_wrapper_restore("php");

        $method = $this->classInspectorProvider->getClassInspector(\Simple::class)->getPublicMethod("autowired");

        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "");

        $this->assertEquals([$request, $request->getUrl(), $request->getHeaders(), $request->getFileUploads()], $handler->handleRoute()->getObject());

    }


    public function testRateLimitingAndCachingRulesArePopulatedFromControllerAnnotationsWhenSet() {


        // Check a method with no caching and class defined rate limit.
        $method = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("list");
        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "");
        $rateLimitConfig = $handler->getRateLimiterConfig();
        $this->assertTrue($rateLimitConfig instanceof RateLimitConfig);
        $this->assertEquals(25, $rateLimitConfig->getRateLimit());
        $this->assertNull($rateLimitConfig->getRateLimitMultiplier());
        $this->assertNull($rateLimitConfig->getRateLimiter());
        $cacheConfig = $handler->getCacheConfig();
        $this->assertNull($cacheConfig);

        // Check a method with overloaded rate limiting
        $method = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("get");
        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "");
        $rateLimitConfig = $handler->getRateLimiterConfig();
        $this->assertTrue($rateLimitConfig instanceof RateLimitConfig);
        $this->assertEquals(50, $rateLimitConfig->getRateLimit());
        $this->assertNull($rateLimitConfig->getRateLimitMultiplier());
        $this->assertNull($rateLimitConfig->getRateLimiter());
        $cacheConfig = $handler->getCacheConfig();
        $this->assertNull($cacheConfig);


        // Now try one with a multiplier.
        $method = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("isTrue");
        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "");
        $rateLimitConfig = $handler->getRateLimiterConfig();
        $this->assertTrue($rateLimitConfig instanceof RateLimitConfig);
        $this->assertNull($rateLimitConfig->getRateLimit());
        $this->assertEquals(2, $rateLimitConfig->getRateLimitMultiplier());
        $this->assertNull($rateLimitConfig->getRateLimiter());
        $cacheConfig = $handler->getCacheConfig();
        $this->assertNull($cacheConfig);


        // Now try a class with a different rate limiter.
        $method = $this->classInspectorProvider->getClassInspector(\Simple::class)->getPublicMethod("handleRequest");
        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "");
        $rateLimitConfig = $handler->getRateLimiterConfig();
        $this->assertTrue($rateLimitConfig instanceof RateLimitConfig);
        $this->assertNull($rateLimitConfig->getRateLimit());
        $this->assertEquals(3, $rateLimitConfig->getRateLimitMultiplier());
        $this->assertEquals("Kinikit\MVC\RateLimiter\TestRateLimiter", $rateLimitConfig->getRateLimiter());
        $cacheConfig = $handler->getCacheConfig();
        $this->assertNull($cacheConfig);


        // Now try a caching one
        $method = $this->classInspectorProvider->getClassInspector(\Simple::class)->getPublicMethod("get");
        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "");
        $rateLimitConfig = $handler->getRateLimiterConfig();
        $this->assertTrue($rateLimitConfig instanceof RateLimitConfig);
        $this->assertNull($rateLimitConfig->getRateLimit());
        $this->assertEquals(3, $rateLimitConfig->getRateLimitMultiplier());
        $this->assertEquals("Kinikit\MVC\RateLimiter\TestRateLimiter", $rateLimitConfig->getRateLimiter());
        $cacheConfig = $handler->getCacheConfig();
        $this->assertNull($cacheConfig->getCache());
        $this->assertEquals("30d", $cacheConfig->getCacheTime());


        // Finally try a class caching one
        include_once "Controllers/Sub/NestedSimple.php";

        $method = $this->classInspectorProvider->getClassInspector(\NestedSimple::class)->getPublicMethod("handleRequest");
        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "");
        $rateLimitConfig = $handler->getRateLimiterConfig();
        $this->assertNull($rateLimitConfig);
        $cacheConfig = $handler->getCacheConfig();
        $this->assertNull($cacheConfig->getCache());
        $this->assertEquals("1d", $cacheConfig->getCacheTime());


    }


}
