<?php


namespace Kinikit\MVC\Routing;


use Kinikit\Core\DependencyInjection\Container;
use Kinikit\Core\Reflection\ClassInspectorProvider;
use Kinikit\MVC\Controllers\REST;
use Kinikit\MVC\Objects\TestRESTObject;
use Kinikit\MVC\Request\Headers;
use Kinikit\MVC\Request\Request;
use Kinikit\MVC\Response\JSONResponse;

class ControllerRouteHandlerTest extends \PHPUnit\Framework\TestCase {

    /**
     * @var ClassInspectorProvider
     */
    private $classInspectorProvider;


    public function setUp(): void {
        $this->classInspectorProvider = Container::instance()->get(ClassInspectorProvider::class);
    }


    public function testCanExecuteAndSendResponseForSimpleRESTGetMethodWithNoParams() {

        $method = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("list");

        $request = new Request(new Headers());

        $handler = new ControllerRouteHandler($method, $request);

        $list = array();
        for ($i = 0; $i < 10; $i++) {
            $list[] = new TestRESTObject("$i", "TEST " . $i, "test$i@test.com");
        }

        $this->assertEquals(new JSONResponse($list), $handler->handleRoute());


    }

}
