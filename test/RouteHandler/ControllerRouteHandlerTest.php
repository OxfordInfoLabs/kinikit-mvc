<?php


namespace Kinikit\MVC\RouteHandler;


use Kinikit\Core\DependencyInjection\Container;
use Kinikit\Core\Exception\WrongParameterTypeException;
use Kinikit\Core\Reflection\ClassInspectorProvider;
use Kinikit\MVC\Controllers\REST;
use Kinikit\MVC\Objects\TestRESTObject;
use Kinikit\MVC\RateLimiter\RateLimiterConfig;
use Kinikit\MVC\Request\Headers;
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

    public function testCanHandleRouteWithBackedEnum() {
        stream_wrapper_unregister("php");
        stream_wrapper_register("php", "Kinikit\MVC\Request\MockPHPInputStream");
        file_put_contents("php://input", '"Uno"');

        // Handle boolean input params properly as well
        $method = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("getBackedEnum");

        $_SERVER["REQUEST_METHOD"] = "POST";

        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "getBackedEnum");

        $this->assertEquals(new JSONResponse("There are Un"), $handler->handleRoute());

        stream_wrapper_restore("php");
    }
    public function testCanHandleRouteWithUnbackedEnum() {
        stream_wrapper_unregister("php");
        stream_wrapper_register("php", "Kinikit\MVC\Request\MockPHPInputStream");
        file_put_contents("php://input", '"One"');

        // Handle boolean input params properly as well
        $method = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("getUnbackedEnum");

        $_SERVER["REQUEST_METHOD"] = "POST";

        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "getUnbackedEnum");

        $this->assertEquals(new JSONResponse("Singular"), $handler->handleRoute());

        stream_wrapper_restore("php");
    }


    public function testExceptionRaisedIfBadPrimitiveParameterTypesPassedForMethod() {
        // Handle boolean input params properly as well
        $method = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("getOnly");

        $_GET["param1"] = "Mark Polo";
        $_GET["param2"] = "bingo";
        $_GET["param3"] = "Goodbye";

        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "getOnly");

        try {
            $handler->handleRoute();
            $this->fail("Should have thrown here");
        } catch (WrongParameterTypeException $e) {
            $this->assertTrue(true);
        }
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


    public function testCanSendBlankArraysAsValidPayloads(){
        stream_wrapper_unregister("php");
        stream_wrapper_register("php", "Kinikit\MVC\Request\MockPHPInputStream");
        file_put_contents("php://input", '[]');

        $method = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("patch");

        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "25");

        $jsonResponse = $handler->handleRoute();
        $this->assertEquals("PATCHED 25", $jsonResponse->getObject()->getLastStatus());


    }


    public function testPostParameterArrayIsUsedInsteadOfPHPInputIfContentTypeIsMultipart() {

        include_once "Controllers/Zone/Simple.php";


        stream_wrapper_restore("php");

        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["CONTENT_TYPE"] = "multipart/form-data; some-extra-data=test";
        $_POST = ["test1" => "Hello", "test2" => "Goodbye", "test3" => "Wonderful"];


        $method = $this->classInspectorProvider->getClassInspector(\Simple::class)->getPublicMethod("classicPost");


        $request = new Request(new Headers());


        $handler = new ControllerRouteHandler($method, $request, "");

        $this->assertEquals(new JSONResponse(["Hello", "Goodbye", "Wonderful"]), $handler->handleRoute());

        unset($_SERVER["CONTENT_TYPE"]);

    }


    public function testCanHandleRouteForViewControllerAndResponseReturnedIntactWithAugmentedRequestParam() {

        include_once "Controllers/Zone/Simple.php";

        $method = $this->classInspectorProvider->getClassInspector(\Simple::class)->getPublicMethod("get");

        $_GET["title"] = "HELLO WORLD";
//        stream_wrapper_restore("php");

        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "");

         $this->assertEquals(new View("Simple", ["title" => "HELLO WORLD", "request" => $request]), $handler->handleRoute());

    }


    public function testRequestObjectsAreAutowiredIfSuppliedToControllerMethods() {

        $_GET = [];
//        stream_wrapper_restore("php");

        $method = $this->classInspectorProvider->getClassInspector(\Simple::class)->getPublicMethod("autowired");

        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "");

        $this->assertEquals([$request, $request->getUrl(), $request->getHeaders(), $request->getFileUploads()], $handler->handleRoute()->getObject());

    }


    public function testRateLimitingAndCachingRulesArePopulatedFromControllerAnnotationsWhenSet() {


        // Check a method with no caching and default rate limiting
        $method = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("list");
        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "");
        $rateLimitConfig = $handler->getRateLimiterConfig();
        $this->assertTrue($rateLimitConfig instanceof RateLimiterConfig);
        $this->assertNull($rateLimitConfig->getRateLimit());
        $this->assertNull($rateLimitConfig->getRateLimitMultiplier());
        $cacheConfig = $handler->getContentCacheConfig();
        $this->assertNull($cacheConfig);

        // Check a method with overloaded rate limiting
        $method = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("get");
        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "");
        $rateLimitConfig = $handler->getRateLimiterConfig();
        $this->assertTrue($rateLimitConfig instanceof RateLimiterConfig);
        $this->assertEquals(50, $rateLimitConfig->getRateLimit());
        $this->assertNull($rateLimitConfig->getRateLimitMultiplier());
        $cacheConfig = $handler->getContentCacheConfig();
        $this->assertNull($cacheConfig);


        // Now try one with a multiplier.
        $method = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("isTrue");
        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "");
        $rateLimitConfig = $handler->getRateLimiterConfig();
        $this->assertTrue($rateLimitConfig instanceof RateLimiterConfig);
        $this->assertNull($rateLimitConfig->getRateLimit());
        $this->assertEquals(2, $rateLimitConfig->getRateLimitMultiplier());
        $cacheConfig = $handler->getContentCacheConfig();
        $this->assertNull($cacheConfig);


        // Now try a class with a different rate limiter.
        $method = $this->classInspectorProvider->getClassInspector(\Simple::class)->getPublicMethod("handleRequest");
        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "");
        $rateLimitConfig = $handler->getRateLimiterConfig();
        $this->assertTrue($rateLimitConfig instanceof RateLimiterConfig);
        $this->assertNull($rateLimitConfig->getRateLimit());
        $this->assertEquals(3, $rateLimitConfig->getRateLimitMultiplier());
        $cacheConfig = $handler->getContentCacheConfig();
        $this->assertNull($cacheConfig);


        // Now try a caching one
        $method = $this->classInspectorProvider->getClassInspector(\Simple::class)->getPublicMethod("get");
        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "");
        $rateLimitConfig = $handler->getRateLimiterConfig();
        $this->assertTrue($rateLimitConfig instanceof RateLimiterConfig);
        $this->assertNull($rateLimitConfig->getRateLimit());
        $this->assertEquals(3, $rateLimitConfig->getRateLimitMultiplier());
        $cacheConfig = $handler->getContentCacheConfig();
        $this->assertEquals("30d", $cacheConfig->getCacheTime());


        // One with default caching
        $method = $this->classInspectorProvider->getClassInspector(\Simple::class)->getPublicMethod("download");
        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "");
        $rateLimitConfig = $handler->getRateLimiterConfig();
        $this->assertTrue($rateLimitConfig instanceof RateLimiterConfig);
        $this->assertNull($rateLimitConfig->getRateLimit());
        $this->assertEquals(3, $rateLimitConfig->getRateLimitMultiplier());
        $cacheConfig = $handler->getContentCacheConfig();
        $this->assertEquals(null, $cacheConfig->getCacheTime());

        // Finally try a class caching one
        include_once "Controllers/Sub/NestedSimple.php";

        $method = $this->classInspectorProvider->getClassInspector(\NestedSimple::class)->getPublicMethod("handleRequest");
        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "");
        $rateLimitConfig = $handler->getRateLimiterConfig();
        $this->assertNull($rateLimitConfig);
        $cacheConfig = $handler->getContentCacheConfig();
        $this->assertEquals("1d", $cacheConfig->getCacheTime());


    }


    public function testAllParametersAreByDefaultSanitised() {

        // Handle boolean input params properly as well
        $method = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("getOnly");

        // Check dangerous tags removed completely
        $_GET["param1"] = '<script type="text/javascript">alert("I am dangerous");</script>';
        $_GET["param2"] = 1.3;
        $_GET["param3"] = true;

        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "getOnly");

        $result = $handler->handleRoute()->getObject();

        $this->assertEquals('', $result[0]);


        // Check regular tags removed as well
        $_GET["param1"] = '<h1>I am less dangerous</h1>';
        $_GET["param2"] = 1.3;
        $_GET["param3"] = true;

        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "getOnly");

        $result = $handler->handleRoute()->getObject();

        $this->assertEquals('I am less dangerous', $result[0]);


        // Check normal characters are restored
        $_GET["param1"] = "Hello@test.com ' \" #£ Hello";
        $_GET["param2"] = 1.3;
        $_GET["param3"] = true;

        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "getOnly");

        $result = $handler->handleRoute()->getObject();

        $this->assertEquals("Hello@test.com ' \" #£ Hello", $result[0]);


        // Check long values are left intact
        $_GET["param1"] = file_get_contents(__DIR__."/large-param.txt");
        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "getOnly");

        $result = $handler->handleRoute()->getObject();

        $this->assertEquals(file_get_contents(__DIR__."/large-param.txt"), $result[0]);


        // Check this happens on payloads recursively too.
        stream_wrapper_unregister("php");
        stream_wrapper_register("php", "Kinikit\MVC\Request\MockPHPInputStream");
        file_put_contents("php://input", '{"id": "23", "name": "<script type=\"text/javascript\">alert(\"bingo\");</script>", "email": "pan@neverland.com", "lastStatus": "SUCCESS"}');

        $method = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("create");

        $request = new Request(new Headers());
        $handler = new ControllerRouteHandler($method, $request, "");

        $this->assertEquals(new JSONResponse(new TestRESTObject("", "pan@neverland.com", "POSTED", 23)), $handler->handleRoute());

        stream_wrapper_restore("php");

    }


    public function testParametersMarkedAsUnsanitiseArePassedThroughWithoutSanitisation() {

        // Check normal characters are restored
        $_GET["param1"] = "<script type='text/javascript'>alert('pingu');</script>Test";
        $_GET["param2"] = "<script type='text/javascript'>alert('pingu');</script>Test";
        $_GET["param3"] = "<script type='text/javascript'>alert('pingu');</script>Test";
        $_GET["param4"] = "<script type='text/javascript'>alert('pingu');</script>Test";

        $request = new Request(new Headers());

        // Handle boolean input params properly as well
        $method = $this->classInspectorProvider->getClassInspector(\Simple::class)->getPublicMethod("sanitiseTest");

        $handler = new ControllerRouteHandler($method, $request, "sanitiseTest");

        $result = $handler->handleRoute()->getObject();

        $this->assertEquals([
            "Test",
            "<script type='text/javascript'>alert('pingu');</script>Test",
            "<script type='text/javascript'>alert('pingu');</script>Test",
            "Test"
        ], $result);

    }

}
