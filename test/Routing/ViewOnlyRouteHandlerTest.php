<?php


namespace Kinikit\MVC\Routing;


use Kinikit\Core\DependencyInjection\Container;
use Kinikit\Core\Template\MustacheTemplateParser;

class ViewOnlyRouteHandlerTest extends \PHPUnit\Framework\TestCase {

    /**
     * @runInSeparateProcess
     */
    public function testExecuteAndStreamResponseSimplyProcessesViewForStaticView() {

        $routeHandler = new ViewOnlyRouteHandler("TestStaticView");

        ob_start();
        $routeHandler->executeAndSendResponse();
        $this->assertEquals(file_get_contents("./Views/TestStaticView.php"), ob_get_contents());
        ob_end_clean();


    }

    /**
     * @runInSeparateProcess
     */
    public function testExecuteAndStreamResponseProcessesViewWithParamsIfPassed() {

        $routeHandler = new ViewOnlyRouteHandler("TestModelView", ["name" => "People", "hobby" => "Maths"]);

        $mustacheParser = Container::instance()->get(MustacheTemplateParser::class);

        ob_start();
        $routeHandler->executeAndSendResponse();
        $model = ["name" => "People", "hobby" => "Maths"];
        $this->assertEquals($mustacheParser->parseTemplateText(file_get_contents("./Views/TestModelView.php"), $model), ob_get_contents());
        ob_end_clean();

    }


}
