<?php


namespace Kinikit\MVC\Response;

use Kinikit\Core\DependencyInjection\Container;
use Kinikit\Core\Template\MustacheTemplateParser;

include_once "autoloader.php";

class ViewTest extends \PHPUnit\Framework\TestCase {


    public function testSimpleStaticViewIsStreamedCorrectly() {

        $view = new View("TestStaticView");

        ob_start();
        include "Views/TestStaticView.php";
        $viewContents = ob_get_contents();
        ob_end_clean();

        ob_start();
        $view->streamContent();
        $this->assertEquals($viewContents, ob_get_contents());
        ob_end_clean();

        $this->assertEquals("text/html", $view->getContentType());
        $this->assertEquals(95, $view->getContentLength());

    }

    public function testMissingViewThrowsExceptionOnEvaluation() {

        try {
            new View("BadView");
            $this->fail("Should have thrown here");
        } catch (ViewNotFoundException $e){
            $this->assertTrue(true);
        }

    }


    public function testViewWithModelIsEvaluatedCorrectly() {

        $view = new View("TestModelView", ["name" => "Marko Polo", "hobby" => "Horse Riding"]);

        $mustacheParser = Container::instance()->get(MustacheTemplateParser::class);
        $model = ["name" => "Marko Polo", "hobby" => "Horse Riding"];
        $expectedOutput = $mustacheParser->parseTemplateText(file_get_contents("Views/TestModelView.php"), $model);


        ob_start();
        $view->streamContent();
        $this->assertEquals($expectedOutput, ob_get_contents());
        ob_end_clean();

        $this->assertEquals("text/html", $view->getContentType());
        $this->assertEquals(139, $view->getContentLength());

    }


}
