<?php


namespace Kinikit\MVC\Routing;


use http\Env\Response;
use Kinikit\Core\DependencyInjection\Container;
use Kinikit\Core\Reflection\ClassInspectorProvider;
use Kinikit\MVC\Controllers\REST;
use Kinikit\MVC\Objects\TestRESTObject;
use Kinikit\MVC\Request\Headers;
use Kinikit\MVC\Request\MockPHPInputStream;
use Kinikit\MVC\Request\Request;
use Kinikit\MVC\Response\JSONResponse;

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


}
