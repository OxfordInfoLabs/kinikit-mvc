<?php


namespace Kinikit\MVC\Routing;


use Kinikit\Core\DependencyInjection\Container;
use Kinikit\Core\Reflection\ClassInspectorProvider;
use Kinikit\MVC\Decorators\Zone;
use Kinikit\MVC\Request\Headers;
use Kinikit\MVC\Request\Request;
use Kinikit\MVC\Response\Download;
use Kinikit\MVC\Response\Redirect;
use Kinikit\MVC\Response\View;

class DecoratorRouteHandlerTest extends \PHPUnit\Framework\TestCase {

    /**
     * @var ClassInspectorProvider
     */
    private $classInspectorProvider;


    public function setUp(): void {
        $this->classInspectorProvider = Container::instance()->get(ClassInspectorProvider::class);
    }

    /**
     * @runInSeparateProcess
     */
    public function testDecoratorRouteHandlerReturnsControllerResponseIntactIfNotViewResponse() {


        // Decorator method
        $decoratorMethod = $this->classInspectorProvider->getClassInspector(Zone::class)->getPublicMethod("handleRequest");

        // Setup a redirect controller route
        $controllerMethod = $this->classInspectorProvider->getClassInspector(\Simple::class)->getPublicMethod("redirect");
        $request = new Request(new Headers());
        $contentRouteHandler = new ControllerRouteHandler($controllerMethod, $request, "");

        $decoratorHandler = new DecoratorRouteHandler($decoratorMethod, $contentRouteHandler, $request);

        $response = $decoratorHandler->handleRoute();
        $this->assertEquals(new Redirect("http://www.google.com"), $response);


        // Setup a download controller route
        $controllerMethod = $this->classInspectorProvider->getClassInspector(\Simple::class)->getPublicMethod("download");
        $request = new Request(new Headers());
        $contentRouteHandler = new ControllerRouteHandler($controllerMethod, $request, "");

        $decoratorHandler = new DecoratorRouteHandler($decoratorMethod, $contentRouteHandler, $request);

        $response = $decoratorHandler->handleRoute();
        $this->assertEquals(new Download("BINGO", "bingo.txt"), $response);


    }

    /**
     * @runInSeparateProcess
     */
    public function testDecoratorRouteHandlerReturnsDecoratorViewWithAugmentedModelContainingContentAndContentModel() {

        // Decorator method
        $decoratorMethod = $this->classInspectorProvider->getClassInspector(Zone::class)->getPublicMethod("handleRequest");

        $_GET["title"] = "BONGO";

        // Setup a redirect controller route
        $controllerMethod = $this->classInspectorProvider->getClassInspector(\Simple::class)->getPublicMethod("get");
        $request = new Request(new Headers());
        $contentRouteHandler = new ControllerRouteHandler($controllerMethod, $request, "");

        $decoratorHandler = new DecoratorRouteHandler($decoratorMethod, $contentRouteHandler, $request);

        $response = $decoratorHandler->handleRoute();

        ob_start();
        $contentRouteHandler->handleRoute()->streamContent();
        $content = ob_get_contents();
        ob_end_clean();

        $this->assertEquals(new View("Zone", ["menu" => "standard", "request" => $request, "content" => $content, "contentModel" => ["title" => "BONGO", "request" => $request]]), $response);


    }

}
