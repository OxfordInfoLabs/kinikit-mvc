<?php


namespace Kinikit\MVC\Routing;


use Kinikit\Core\DependencyInjection\Container;
use Kinikit\Core\Template\MustacheTemplateParser;
use Kinikit\MVC\Response\View;

class ViewOnlyRouteHandlerTest extends \PHPUnit\Framework\TestCase {

    /**
     * @runInSeparateProcess
     */
    public function testExecuteAndStreamResponseSimplyProcessesViewForStaticView() {

        $routeHandler = new ViewOnlyRouteHandler("TestStaticView");
        $this->assertEquals(new View("TestStaticView"), $routeHandler->handleRoute());
    }

    /**
     * @runInSeparateProcess
     */
    public function testExecuteAndStreamResponseProcessesViewWithParamsIfPassed() {

        $routeHandler = new ViewOnlyRouteHandler("TestModelView", ["name" => "People", "hobby" => "Maths"]);
        $this->assertEquals(new View("TestModelView", ["name" => "People", "hobby" => "Maths"]), $routeHandler->handleRoute());

    }


}
