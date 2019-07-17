<?php


namespace Kinikit\MVC\RouteHandler;


use Kinikit\Core\DependencyInjection\Container;
use Kinikit\Core\Template\MustacheTemplateParser;
use Kinikit\MVC\Request\Headers;
use Kinikit\MVC\Request\Request;
use Kinikit\MVC\Response\View;

class ViewOnlyRouteHandlerTest extends \PHPUnit\Framework\TestCase {

    /**
     * @runInSeparateProcess
     */
    public function testExecuteAndStreamResponseSimplyProcessesViewForStaticView() {

        $request = new Request(new Headers());
        $routeHandler = new ViewOnlyRouteHandler("TestStaticView", $request);
        $this->assertEquals(new View("TestStaticView", ["request" => $request]), $routeHandler->handleRoute());
    }



}
